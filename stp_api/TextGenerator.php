<?php
namespace spamtonprof\stp_api;

/**
 *
 * @author alexg
 *        
 */
class TextGenerator implements \JsonSerializable
{

    public function __construct()
    
    {}

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    public function generate_markov_table($text, $order)
    {
        
        // walk through the text and make the index table for words
        $wordsTable = explode(' ', trim($text));
        $table = array();
        $tableKeys = array();
        $i = 0;
        
        foreach ($wordsTable as $key => $word) {
            $nextWord = "";
            for ($j = 0; $j < $order; $j ++) {
                if ($key + $j + 1 != sizeof($wordsTable) - 1)
                    if (array_key_exists($key + $j + 1, $wordsTable)) {
                        $nextWord .= " " . $wordsTable[$key + $j + 1];
                    }
            }
            if (! isset($table[$word . $nextWord])) {
                $table[$word . $nextWord] = array();
            }
            ;
        }
        
        $tableLength = sizeof($wordsTable);
        
        // walk the array again and count the numbers
        for ($i = 0; $i < $tableLength - 1; $i ++) {
            $word_index = $wordsTable[$i];
            $word_count = $wordsTable[$i + 1];
            if (isset($table[$word_index][$word_count])) {
                $table[$word_index][$word_count] += 1;
            } else {
                $table[$word_index][$word_count] = 1;
            }
        }
        return $table;
    }

    private function sentenceBegin($str)
    {
        return $str == ucfirst($str) && preg_match('#^[A-Z1-9].*#', $str) == 1;
    }

    private function generate_markov_text($length, $table)
    {
        // get first word
        do {
            $word = array_rand($table);
        } while (! $this->sentenceBegin($word));
        
        $o = $word;
        
        while (strlen($o) < $length) {
            $newword = $this->return_weighted_word($table[$word]);
            
            if ($newword) {
                $word = $newword;
                $o .= " " . $newword;
            } else {
                do {
                    $word = array_rand($table);
                } while (! $this->sentenceBegin($word));
            }
        }
        
        return $o;
    }

    public function generate_text($input, $length, $order)
    {
        $generated_text = "";
        if ($input)
            $text = $input;
        
        if (isset($text)) {
            $markov_table = $this->generate_markov_table($text, $order);
            $generated_text = $this->generate_markov_text($length, $markov_table, $order);
            
            if (get_magic_quotes_gpc())
                $generated_text = stripslashes($markov);
        }
        
        return ($generated_text);
    }

    public function generate_article(array $images, $text_body, $text_title, $model = 1)
    {
        $words_body = explode(" ", $text_body);
        $words_title = explode(" ", $text_title);
        
        $article = "";
        
        if ($model == 1) {
            
            $article = $this->gene_h2($this->gene_phrase($words_body, 10));
        }
        
        return ($article);
    }

    public function gene_phrase(array $words, $length)
    {
        $phrase = [];
        
        for ($i = 0; $i < $length; $i ++) {
            $word = array_shift($words);
            $phrase[] = $word;
        }
        
        return (implode(" ", $phrase));
    }

    public function gene_h2($text)
    {
        return ("<h2>$text</h2>");
    }

    private function return_weighted_word($array)
    {
        if (! $array)
            return false;
        
        $total = array_sum($array);
        $rand = mt_rand(1, $total);
        foreach ($array as $item => $weight) {
            if ($rand <= $weight)
                return $item;
            $rand -= $weight;
        }
    }
}

