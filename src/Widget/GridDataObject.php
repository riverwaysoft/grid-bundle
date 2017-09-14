<?php

namespace RiverwayGrid\Widget;

class GridDataObject
{
    private $head = [];
    private $body;

    /**
     * @return mixed
     */
    public function getHead(): array
    {
        return $this->head;
    }

    /**
     * @param mixed $head
     *
     * @return $this
     */
    public function setHead($head)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitles(): array
    {
        $res = [];
        foreach ($this->getHead() as $item) {
            if ($item['title']) {
                $res[] = $item['title'];
            } else {
                $res[] = $item['id'];
            }
        }

        return $res;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }
}