<?php
namespace spamtonprof\stp_api;

class WpMg

{

    public $sh = "";

    public function __construct()

    {
        $script = "";
    }

    /* update link on network */
    // $this = new \spamtonprof\stp_api\WpMg();
    // $this->update_links_on_network($domains, $target_subdomains);
    // $this->execute_remote();
    // exit();
    public function update_links_on_network($domains, $target_subdomains)
    {
        foreach ($target_subdomains as $target_subdomain) {

            $this->update_links_on_subdomains($domains, $target_subdomain, $target_subdomains);
        }

        echo (nl2br($this->sh));

        $this->execute_remote();
    }

    public function update_links_on_subdomains($domains, $target_sub, $target_subdomains)
    {
        $pre_subdomains = $target_subdomains;

        // $this -> clone_wp($domain, $domain_to_clone, $path_to_clone);

        foreach ($domains as $domain) {

            $wd = "/home/yopla/" . $target_sub . "." . $domain;

            if ($target_sub == "") {
                $wd = "/home/yopla/" . $domain;
            }

            if (($key = array_search($target_sub, $pre_subdomains)) !== false) {
                unset($pre_subdomains[$key]);
            }

            $this->add_subdomains_link($wd, $domain, $pre_subdomains);

            // $this->remove_cache($wd);
        }
    }

    // pour installer en clonant un nouveau wordpress et configurer catégories, blog description , blog title
    public function install_wp_new_domains($domains, $target_subdomains, $categories,$slug_menus,$blog_description,$blog_name)
    {

        for ($i = 0; $i < count($categories); $i ++) {
            $categories[$i] = quote($categories[$i]);
        }
        
        $domains = array_map('strtolower', $domains);
        $domains = array_map('trim', $domains);
        

        $blog_description = quote($blog_description);
        $blog_name = quote($blog_name);

        foreach ($domains as $domain) {
            foreach ($target_subdomains as $target_subdomain) {

                $cpanel = new \spamtonprof\stp_api\CpanelMg();

                $wd = "/home/yopla/" . $target_subdomain . "." . $domain;
                $final_domain = $target_subdomain . "." . $domain;
                if ($target_subdomain == "") {
                    $wd = "/home/yopla/" . $domain;
                    $final_domain = $domain;
                } else {
                    $cpanel->add_sub_domain($target_subdomain, $domain);
                }

                $this->clone_wp($final_domain, "zergling.bien-etre-facile.net", "/home/yopla/zergling.bien-etre-facile.net");

                $this->remove_all_categories($final_domain);
                $this->create_categories($final_domain, $categories);

                foreach ($slug_menus as $slug_menu) {
                    $this->add_all_categories_to_menu($final_domain, $slug_menu);
                }

                $this->update_blog_description($final_domain, $blog_description);
                $this->update_blog_name($final_domain, $blog_name);

                $this->deactivate_plugin($wd, "wp-rocket");
                $this->activate_plugin($wd, "wp-rocket");

                $this->disable_wp_cron($final_domain);

                $this->remove_cache($final_domain);

                // // $this->create_bhm_campaigns($domain);
            }
        }
        
    }

    // $domain est le nom de domaine principale
    // $subdomains est le prefixe à mettre avant $domain pour faire un sous de domaine complet
    public function add_subdomains_link($wd, $domain, array $subdomains)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/add_widget_links.sh");

        $links = "";

        foreach ($subdomains as $subdomain) {

            $subdomain_tempo = $domain;
            if ($subdomain != "") {
                $subdomain_tempo = $subdomain . '.' . $domain;
            }

            $links = $links . '<li><a href="https://' . $subdomain_tempo . '">' . $subdomain_tempo . '</a></li>';
        }

        $links = '<ul>' . $links . '</ul>';

        $sh = str_replace("[[links]]", $links, $sh);
        $sh = str_replace("[[wppath]]", $wd, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function create_bhm_campaigns($domain, $dir = false)
    {
        $rows = readCsv("/var/www/html/spamtonprof/wp-content/plugins/spamtonprof/tempo/export.csv", ",");

        array_shift($rows);

        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $params = array(
            'dir' => $dir,
            'minutes' => 1,
            'maillage' => 0,
            'nb_posts' => 500
        );

        $campaigns = [];

        foreach ($rows as $row) {

            $campaigns[] = array(
                'cat_id' => $row[0],
                'url' => $row[3]
            );
        }

        $sh = "";
        foreach ($campaigns as $campaign) {
            $params['cat_id'] = $campaign['cat_id'];
            $params['url'] = $campaign['url'];

            $sh = $sh . $this->add_bhm_campaign($params);
        }

        return ($sh);
    }

    public function get_all_categories($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/get_all_categories.sh");
        $sh = str_replace("[[dir]]", $dir, $sh);

        $this->add_to_sh($sh);

        $res = $this->execute_remote(true);

        $categories = json_decode($res);

        return ($categories);
    }

    public function get_bhm_options($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/get_bhm_options.sh");
        $sh = str_replace("[[dir]]", $dir, $sh);

        $this->add_to_sh($sh);

        $res = $this->execute_remote(true);

        $options_bhm = json_decode($res);

        return ($options_bhm);
    }

    public function update_options($domain, $option, $json, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/update_options.sh");
        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[option]]", $option, $sh);
        $sh = str_replace("[[json]]", $json, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    /*
     * Exemple d'utlisation
     *
     *
     
            $domains = ["claire-desbois.fr"];
            $wpMg->activate_all_bhm_campaigns($domains[0]);
            $wpMg->execute_remote(true,true);

     *
     *
     */
    public function activate_all_bhm_campaigns($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $options_bhm = $this->get_bhm_options($domain, $dir);

        prettyPrint($options_bhm);
        
        $campaigns_categories = $options_bhm->generator->category;

        foreach ($campaigns_categories as $campaign_categorie) {
            $campaign_categorie->activated = 1;
        }

        $this->update_options($domain, 'bhs_generator_settings', json_encode($options_bhm), $dir);
        
    }

    /*
     * Exemple d'utlisation
     *
     *
     * $domains = ["claire-desbois.fr"];
     * $bhm_ids = ['1578','1579','1581','1582','1583','1584','1585'];
     * $categories = ["Traitement arthrose","Augmentation de la libido","Anti rides","Extension du pénis","Traitement calvitie","Restauration de l'audition","Traitement des hallux"];
     * $wpMg->add_bhm_campaign($domains[0],$categories, $bhm_ids);
     * $wpMg->execute_remote(true,true);
     * exit();
     *
     *
     */
    public function add_bhm_campaigns($domain, $categorie_names, $bhm_campaigns, $dir = false)
    {
        $bhm_ids_by_cat = [];

        for ($i = 0; $i < count($categorie_names); $i ++) {
            $categorie_name = $categorie_names[$i];
            $bhm_ids_by_cat[$categorie_name] = $bhm_campaigns[$i];
        }

        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $options_bhm = $this->get_bhm_options($domain, $dir);
        $categories = $this->get_all_categories($domain, $dir);

        // prettyPrint($options_bhm->generator->category);

        $campaigns_categories = new \stdClass();

        foreach ($categories as $categorie) {

            $slug = $categorie->slug;
            $term_id = $categorie->term_id;

            $best_score = 0;
            $url_winner = false;
            $cat_id_winner = false;
            foreach ($bhm_ids_by_cat as $categorie_name => $bhm_id) {
                $score = similar_text($slug, sanitize_text_field($categorie_name));
                if ($score > $best_score) {
                    $url_winner = $bhm_id;
                    $cat_id_winner = $term_id;
                    $best_score = $score;
                }
            }

            $raw_campaign = new \stdClass();
            $raw_campaign->thematic = $url_winner;
            $raw_campaign->post_number = 150;
            $raw_campaign->activated = 0;

            $campaigns_categories->{strval($cat_id_winner)} = $raw_campaign;
        }

        // prettyPrint($campaigns_categories);

        $options_bhm->generator->category = $campaigns_categories;

        // prettyPrint($options_bhm);

        $this->update_options($domain, 'bhs_generator_settings', json_encode($options_bhm), $dir);
    }

    /*
     * Exemple d'utlisation
     *
     * $domains = ["claire-desbois.fr"];
     * $categories = ["Traitement arthrose","Augmentation de la libido","Anti rides","Extension du pénis","Traitement calvitie","Restauration de l'audition","Traitement des hallux"];
     * $urls = ['https://w.lemonetik.com/index.php?id_promo=5030071_4&promokeys=2e788801cbceb8ddfed6d0871bd122b1','https://www.spray-x.fr/?c=72&utm_medium=cpa&utm_source=mxspfr&utm_campaign=spx01&utm_content=431388','https://fr.royalageless24.com/promotion/royal_fr/peau-rajeunie-de-15-ans/?_url=%252Fr%252FDe6VIofSWB-6TwZFaVnhG&utm_campaign=20488&utm_content=12712&utm_medium=17&utm_source=426&utm_sub_id=1677917778&utm_term=431388&sub_id=g1oqiqo7e51hdjdh5nlk98nhc2&adi=A3709ec07a18a14f6db88cb477648a637&adref=De6VIofSWB-6TwZFaVnhG&adrf=431388&adp=5170&ada=&clear_stats=De6VIofSWB-6TwZFaVnhG&adr=eHRyb2dsb2JhbC5jb20%3D','https://fr.centredepromotion.com/promotion/collosel_fr/en-seulement-30-jours/?_url=%252Fr%252FX2DIcJMe9f-U42VgTaWhx&utm_campaign=19492&utm_content=17467&utm_medium=17&utm_source=426&utm_sub_id=1677918847&utm_term=431388&sub_id=g1oqiqo7e51hdjdh5nlk98nhc2&adi=A3709ec07a18a14f6db88cb477648a637&adref=X2DIcJMe9f-U42VgTaWhx&adrf=431388&adp=8464&ada=&clear_stats=X2DIcJMe9f-U42VgTaWhx&adr=eHRyb2dsb2JhbC5jb20%3D','http://dahit.co/xo56px?rpt=sp&aid=174768&token=lqwz2iag9zDA10511DAFR&rfc=FR','http://dahit.co/Ed0rVa?rpt=br&aid=174768&token=lqwz2iag9zDA10823DAFR&rfc=FR','http://dahit.co/wItd3k?rpt=br&aid=174768&token=lqwz2iag9zDA10810DAFR&rfc=FR'];
     * $wpMg->update_url_cloacking($domains[0],$categories,$urls);
     *
     */
    public function update_url_cloacking($domain, $categorie_names, $urls, $dir = false)
    {
        $urls_by_cat = [];

        for ($i = 0; $i < count($categorie_names); $i ++) {
            $categorie_name = $categorie_names[$i];
            $urls_by_cat[$categorie_name] = $urls[$i];
        }

        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $options_bhm = $this->get_bhm_options($domain, $dir);
        $categories = $this->get_all_categories($domain, $dir);

        $url_categories = new \stdClass();

        foreach ($categories as $categorie) {

            $slug = $categorie->slug;
            $term_id = $categorie->term_id;

            $best_score = 0;
            $url_winner = false;
            $cat_id_winner = false;
            foreach ($urls_by_cat as $categorie_name => $url) {
                $score = similar_text($slug, sanitize_text_field($categorie_name));
                if ($score > $best_score) {
                    $url_winner = $url;
                    $cat_id_winner = $term_id;
                    $best_score = $score;
                }
            }

            $raw_url = new \stdClass();
            $raw_url->url = $url_winner;
            $url_categories->{strval($cat_id_winner)} = $raw_url;
        }

        $options_bhm->cloaking->redirection->category = $url_categories;

        $this->update_options($domain, 'bhs_generator_settings', json_encode($options_bhm), $dir);
    }

    public function create_categories($domain, $categories, $dir = false)
    {
        foreach ($categories as $categorie) {

            $this->create_category($domain, $categorie, $categorie);
        }
    }

    public function remove_all_categories($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/remove_all_categories.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function create_category($domain, $nom_cat, $description, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/create_category.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[nom]]", $nom_cat, $sh);
        $sh = str_replace("[[desc]]", $description, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function add_all_categories_to_menu($domain, $slug_menu, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/add_all_categories_to_menu.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[slug_menu]]", $slug_menu, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function update_blog_name($domain, $blog_name, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/update_blog_name.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[blog_name]]", $blog_name, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function update_blog_description($domain, $blog_description, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/update_blog_description.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[blog_description]]", $blog_description, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function disable_wp_cron($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/enable_wp_cron.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function remove_cache($domain, $dir = false)
    {
        if (! $dir) {
            $dir = "/home/yopla/" . $domain;
        }

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/clear_cache.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function install_wprocket_cli($wd)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/install_wp_rocket_cli.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);

        return ($sh);
    }

    public function deactivate_plugin($wd, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/deactivate_plugin.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function add_to_style_sheet($domain, $theme, $path_file)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/append_stylecss.sh");

        $sh = str_replace("[[domain]]", $domain, $sh);

        $sh = str_replace("[[theme]]", $theme, $sh);

        $sh = str_replace("[[path_file]]", $path_file, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function fix_guttensearch()
    {
        $filePath = 'shortcode.csv';
        $rows = readCsv($filePath, ",");
        $content_ori = file_get_contents("gutensearch_replace.html");

        for ($i = 0; $i < count($rows); $i ++) {
            if ($i == 0) {
                continue;
            }

            $row = $rows[$i];
            $replace = $row[0];
            $keyword = $row[1];

            // $keyword =str_replace("'", "\'", $keyword);

            $link = $row[2];
            $content = $content_ori;
            $content = str_replace('[kw]', '"' . ucfirst($keyword) . '"', $content);
            $content = str_replace('spamtonprof.com', $link, $content);

            // echo($content);
            // exit();

            $this->search_replace($final_domain, "'" . $replace . "'", "'" . $content . "'");

            // $this->search_replace($final_domain,'testtttttttttttttttttttttttt', '"'.'test ' . 'test ' . 'test ' . 'test ' .'"');
        }

        $this->deactivate_plugin($final_domain, 'header-footer');

        $this->remove_cache($domain);
    }

    public function search_replace($domain, $search, $replace)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/search_replace.sh");

        $sh = str_replace("[[domain]]", $domain, $sh);

        $sh = str_replace("[[search]]", $search, $sh);

        $sh = str_replace("[[replace]]", $replace, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function count_post($domain)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/count_post.sh");

        $sh = str_replace("[[domain]]", $domain, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function add_cron($domain)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/add_cron.sh");

        $sh = str_replace("[[domain]]", $domain, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function activate_plugin($wd, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/activate_plugin.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function install_plugins($wd, $plugin_path, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/install_plugins.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);
        $sh = str_replace("[[plugin_path]]", $plugin_path, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function add_bhm_campaign($params)
    {
        $dir = $params['dir'];
        $url = $params['url'];
        $cat_id = $params['cat_id'];
        $nb_posts = $params['nb_posts'];
        $minutes = $params['minutes'];
        $maillage = $params['maillage'];

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/add_bgm_campaign.sh");

        $sh = str_replace("[[dir]]", $dir, $sh);
        $sh = str_replace("[[token]]", $url, $sh);
        $sh = str_replace("[[cat_id]]", $cat_id, $sh);
        $sh = str_replace("[[nb_posts]]", $nb_posts, $sh);
        $sh = str_replace("[[minutes]]", $minutes, $sh);
        $sh = str_replace("[[maillage]]", $maillage, $sh);

        $this->add_to_sh($sh);
    }

    public function clone_wp($domain, $domain_to_clone, $path_to_clone, $pathtoinstall = false)
    {
        $cpanel = new \spamtonprof\stp_api\CpanelMg();

        $password = $domain . '____' . $domain;

        $db = $cpanel->create_data_base_with_admin($domain, $password);

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/clone_wordpress_wp_cli.sh");

        $email = 'bof.affiliate@gmail.com';
        $username = "bofs";

        if (! $pathtoinstall) {
            $pathtoinstall = "/home/yopla/$domain";
        }

        $dbname = $db["db_name"];
        $dbpass = $db["password"];
        $wptitle = "Banques";
        $host = "world-387.fr.planethoster.net";

        $sh = str_replace("[[pathtoclone]]", $path_to_clone, $sh);

        $sh = str_replace("[[email]]", $email, $sh);
        $sh = str_replace("[[url]]", $domain_to_clone, $sh);
        $sh = str_replace("[[cloneurl]]", $domain, $sh);
        $sh = str_replace("[[username]]", $username, $sh);
        $sh = str_replace("[[pathtoinstall]]", $pathtoinstall, $sh);
        $sh = str_replace("[[dbname]]", $dbname, $sh);
        $sh = str_replace("[[dbpass]]", $dbpass, $sh);
        $sh = str_replace("[[wptitle]]", $wptitle, $sh);
        $sh = str_replace("[[host]]", $host, $sh);

        $this->add_to_sh($sh);

        return ($sh);
    }

    public function add_to_sh($sh)
    {
        $this->sh = $this->sh . PHP_EOL . $sh;
    }

    public function execute_remote($execute = false, $verbose = false)
    {
        $sh1 = $this->sh;
        $sh1 = str_replace("cmdwp", "/usr/local/cpanel/3rdparty/bin/wp", $sh1);
        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/tempo.sh", $sh1);

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/execute_sh_via_ssh.sh");

        $sh = str_replace("[[file]]", "tempo.sh", $sh);
        $sh = str_replace("[[cmd]]", ". ~/sh/tempo.sh", $sh);

        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/execute_sh_via_ssh.sh", $sh);

        if ($verbose) {
            echo (nl2br($sh1));
        }

        if ($execute) {

            $ssh = new \phpseclib\Net\SSH2('hybrid2313.fr.ns.planethoster.net', 2908);
            if (! $ssh->login('yopla', 'H.Rcj>m6pBfh')) {
                exit('Login Failed');
            }

            $sftp = new \phpseclib\Net\SFTP('hybrid2313.fr.ns.planethoster.net', 2908);
            if (! $sftp->login('yopla', 'H.Rcj>m6pBfh')) {
                exit('Login Failed');
            }

            $sftp->put('/home/yopla/sh/tempo.sh', $sh1);

            $ssh->setTimeout(10000000);
            
            $res = $ssh->exec('/home/yopla/sh/tempo.sh');

            echo (nl2br($res));

            $this->sh = "";

            return ($res);
        }
    }
}