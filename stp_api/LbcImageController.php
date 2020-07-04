<?php
namespace spamtonprof\stp_api;

class LbcImageController

{

    private $_db;

    // Instance de PDO
    public function __construct()

    {
        \Cloudinary::config(array(
            "cloud_name" => "spamtonprof",
            "api_key" => "125351984184831",
            "api_secret" => "LoAe022XCAF6FnckOSZD7W2XrSE",
            "secure" => true
        ));
    }

    public function list_img_folders()
    {
        $lbc_folder = ABSPATH . "wp-content/uploads/lbc_images/";

        $paths = dir($lbc_folder);

        while (false !== ($entry = $paths->read())) {
            echo $entry . "<br>";
        }

        exit();
    }

    public function retrieve_cloudinary()
    {
        $api = new \Cloudinary\Api();

        $uploads = $api->resources(array(
            "type" => "upload",
            "prefix" => "lbc/",
            "max_results" => 500
        ));

        $uploads = $uploads['resources'];

        $date = new \DateTime("", new \DateTimeZone("Europe/Paris"));

        foreach ($uploads as $upload) {

            // prettyPrint($upload);

            $public_id = explode("/", $upload['public_id']);

            $lbc_folder = $public_id[count($public_id) - 2];
            $img_name = $public_id[count($public_id) - 1];

            $lbc_folder = ABSPATH . "wp-content/uploads/lbc_images/" . $date->format(PG_DATE_FORMAT) . "_" . $lbc_folder;

            mkdir($lbc_folder);

            file_put_contents($lbc_folder . "/" . $img_name . "." . $upload['format'], file_get_contents($upload['url']));

            $api->delete_resources($upload["public_id"]);

            // voir si le folder existe sinon le créer
            // downloader l'image
            // supprimer l'image dans cloudinaray
        }
    }

    public function push_cloudinary()
    {
        $baseDir = new \DirectoryIterator(ABSPATH . "images");
        foreach ($baseDir as $fileinfo) {
            if (! $fileinfo->isDot()) {

                $image_folder = new \DirectoryIterator($fileinfo->getPathname());

                foreach ($image_folder as $imageinfo) {

                    if (! $imageinfo->isDot()) {

                        $extension = array_pop(explode(".", $imageinfo->getFilename()));

                        $new_name = generateRandomString(20);
                        $new_name = $imageinfo->getPath() . "/" . $new_name . "." . $extension;

                        rename($imageinfo->getPathname(), $new_name);

                        \Cloudinary\Uploader::upload($new_name, array(
                            "width" => 400,
                            "crop" => "scale",
                            "folder" => "lbc/" . $fileinfo->getFilename(),
                            "quality" => "auto:low"
                        ));
                    }
                }
            }
        }
    }
}