<?php

namespace usernamefilter;

class WordList {
    
    /** @var string */
    private $filename;
    /** @var string[] */
    private $array = array();
    
    public function __construct(string $filename) {
        $exists = file_exists($filename);
        $this->filename = $filename;
        if (!$exists) {
            fopen($filename, "w");
        } else {
            $this->reload();
        }
    }
    
    public function add(string $word) {
        if (in_array($word, $this->array)) {
            return;
        }
        $this->array[] = strtolower($word);
        $this->save();
    }
    
    public function remove(string $word) {
        if (!in_array(strtolower($word), $this->array)) {
            return;
        }
        unset($this->array[array_search(strtolower($word), $this->array)]);
        $this->save();
    }
    
    public function forEachWord() {
        foreach ($this->array as $k) {
            yield $k;
        }
    }
    
    public function contains(string $word) {
        return in_array(strtolower($word), $this->array);
    }
    
    private function yieldWords() {
        foreach (explode(", ", file_get_contents($this->filename)) as $word) {
            yield $word;
        }
    }
    
    public function reload() {
        if (!file_exists($this->filename)) {
            fopen($this->filename, "w");
            $this->array = array();
        }
        foreach ($this->yieldWords() as $words) {
            $this->array[] = strtolower($words);
        }
    }
    
    public function save() {
        $string = "";
        foreach ($this->array as $array) {
            $string .= strtolower($array);
            $string .= ", ";
        }
        $str = substr($string, 0, strlen($string) - 2);
        if (!file_exists($this->filename)) {
            fopen($this->filename, "w");
        }
        file_put_contents($this->filename, $str);
    }
}