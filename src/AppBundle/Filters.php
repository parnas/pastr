<?php

namespace AppBundle;


class Filters
{
    protected $from, $till, $id;


    public function __construct(array $data = array())
    {
        $this->fromArray($data);
    }

    /**
     * @return mixed
     */
    public function getFrom($format = 'U')
    {
        return date($format, $this->from);
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = strtotime($from);
    }

    /**
     * @return mixed
     */
    public function getTill($format = 'U')
    {
        return date($format, $this->till);
    }

    /**
     * @param mixed $till
     */
    public function setTill($till)
    {
        $this->till = strtotime($till);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    public function fromArray($data)
    {
        foreach ($data as $attribute => $value) {
            $setter = 'set' . ucfirst($attribute);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }
}