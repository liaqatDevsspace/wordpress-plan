<?php

class Book
{
    private $title;
    private $author;
    private $price;

    public function __construct($title, $author, $price)
    {
        $this->title = $title;
        $this->author = $author;
        $this->price = $price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

$b1 = new Book("Fredom", "Liaqat Ali", 21.39);
echo "Title: " . $b1->getTitle() . ", ";
echo "Author: " . $b1->getAuthor() . ", ";
echo "Price: " . $b1->getPrice() . "$.";
