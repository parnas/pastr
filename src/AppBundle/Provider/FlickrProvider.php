<?php

namespace AppBundle\Provider;

use AppBundle\Entity\Image;

class FlickrProvider
{
    protected $key, $secret;
    protected $endpoint = 'https://api.flickr.com/services/rest/';
    protected $client;

    protected $allExtras = 'description, license, date_upload, date_taken, owner_name, icon_server,
    original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias,
    url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o';

    protected $extras = 'date_upload, date_taken, url_z, url_l, url_o';


    public function __construct($client, $key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->client = $client;
    }

    public function call($method, $parameters = array())
    {
        $parameters['method'] = 'flickr.'.$method;
        $parameters['format'] = 'php_serial';
        $parameters['api_key'] = $this->key;
        $parameters['extras'] = $this->extras;

        if(isset($parameters['nickname'])) {
            $parameters['user_id'] = $this->getUserId($parameters['nickname']);
            unset($parameters['nickname']);
        }

        $response = $this->client->get($this->endpoint, ['query' => $parameters]);

        return unserialize((string)$response->getBody());
    }

    public function getUserId($nickname)
    {
        $reponse = $this->call('people.findByUsername', array('username' => $nickname));

        return $reponse['user']['id'];

    }

    public function search($filters = null)
    {
        $translations = array(
            'from' => array(
                'name' => 'min_upload_date',
                'parameters' => array('Y-m-d')
            ),
            'till' => array(
                'name' => 'max_upload_date',
                'parameters' => array('Y-m-d')
            ),
            'id' => 'nickname'
        );

        $filters = $this->translate($filters, $translations);
        $page = 0;

        do {
            $page++;
            $filters['page'] = $page;
            $response = $this->call('photos.search', $filters);

            if (isset($response['photos']['photo'])) {
                foreach ($response['photos']['photo'] as $photo) {
                    $sizeOrder = ['o', 'l', 'z'];

                    $url = '';
                    foreach ($sizeOrder as $size) {
                        if (isset($photo['url_' . $size])) {
                            $url = $photo['url_' . $size];
                            break;
                        }
                    }

                    if ($url) {
                        $image = new Image();
                        $image->setProvider('flickr');
                        $image->setUrl($url);
                        $image->setProviderId($photo['id']);
                        $image->setDateFromTimestamp($photo['dateupload']);
                        $image->setTitle($photo['title']);
                        $image->setOwnerId($photo['owner']);

                        yield $image;
                    }
                }
            }
        } while ($page - $response['photos']['pages'] != 0);
    }

    protected function translate($filters, $translations)
    {
        $result = array();

        foreach($translations as $from => $to) {
            $method = 'get'.ucfirst($from);
            if(method_exists($filters, $method)) {
                if(is_array($to)) {
                    $result[$to['name']] = call_user_func_array(array($filters, $method), $to['parameters']);
                } else {
                    $result[$to] = $filters->$method();
                }
            }
        }

        return $result;
    }
}