<?php

namespace AppBundle\Controller;

use AppBundle\Filters;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/get", name="get")
     */
    public function getAction(Request $request)
    {
        $filters = new Filters($request->query->all());

        $provider = $this->get('app.flickr');
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Image');

        $images = array();
        foreach($provider->search($filters, true) as $image) {
            $exists = $repository->findOneBy(array(
                'provider' => $image->getProvider(),
                'providerId' => $image->getProviderId()
            ));

            if(!$exists) {
                $em->persist($image);
            }

            $images[] = $image;
        }

        $em->flush();

        return new JsonResponse(array('count' => count($images), 'image' => $images[array_rand($images)]->getUrl()));
    }
}
