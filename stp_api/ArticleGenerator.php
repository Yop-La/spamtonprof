<?php
namespace spamtonprof\stp_api;

/**
 *
 * @author alexg
 *        
 */

use Gregwar\Image\Image;

class ArticleGenerator implements \JsonSerializable
{

    protected $article = "", $words_body, $words_title, $images;

    public function __construct()
    
    {}

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    public function write($html)
    {
        $this->article = $this->article . $html;
    }

    public function generate_article(array $images, $text_body, $text_title, $model = 1)
    {
        $this->article = "";
        $this->images = $images;
        $this->words_body = explode(" ", $text_body);
        $this->words_title = explode(" ", $text_title);
        
        if ($model == 1) {
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());

            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));

            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            $this->write($this->get_h2_tag($this->gene_title(7, "title")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->get_p_tag($this->gene_phrase(random_int(45, 65), "body")));
            
            $this->write($this->gene_image_tag());
            
            
            // $this->write($this->get_p_tag($this->gene_phrase(10, "title")));
        }
    }

    
    
    public function get_article()
    {
        return ($this->article);
    }

    public function get_img_tag($path)
    {
        return ("<img src=\"$path\" alt=\"image " . $this->gene_title(3) . "\" title=\"image " . $this->gene_title(3) . "\"/><br>");
    }
    
    public function gene_image_tag(){
        $img_tag = "";
        if(count($this->images) != 0){
            
            
            $img_path = array_shift($this->images);
            $img_tag = $this->get_img_tag($img_path);
            
        }
        return($img_tag);
    }

    public function gene_title($length)
    {
        $phrase = [];
        
        for ($i = 0; $i < $length; $i ++) {
            $word = array_shift($this->words_title);
            $word = str_replace(".", "", $word);
            $phrase[] = $word;
        }
        
        return (ucfirst(implode(" ", $phrase)));
    }

    public function gene_phrase($length)
    {
        $phrase = [];
        
        for ($i = 0; $i < $length; $i ++) {
            $word = array_shift($this->words_body);
            $phrase[] = $word;
        }
        
        return (ucfirst(implode(" ", $phrase)));
    }

    public function get_h2_tag($text)
    {
        return ("<h2>$text</h2>");
    }

    public function get_p_tag($text)
    {
        return ("<p>$text</p>");
    }
}

