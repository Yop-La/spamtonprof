<?php
namespace spamtonprof\stp_api;

class WpMg

{

    public $sh = "";

    public function __construct()

    {
        $script = "";
    }

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

        // $wpmg -> clone_wp($domain, $domain_to_clone, $path_to_clone);
        
        foreach ($domains as $domain) {

            $wd = "/home/yopla/" . $target_sub . "." . $domain;

            if ($target_sub == "") {
                $wd = "/home/yopla/" . $domain;
            }

            if (($key = array_search($target_sub, $pre_subdomains)) !== false) {
                unset($pre_subdomains[$key]);
            }

            $this->add_subdomains_link($wd, $domain, $pre_subdomains);

            
            
            
//             $this->remove_cache($wd);
            
        }
        
        

    }

//    Exemples d'utilisation
//     $domains = [
//         "artdumariage-paris.com","bien-etre-facile.net","breizhtrotteuse.com","chatmallowc.com","clopipop.com","expertise-marchespublics.fr"
//     ];
    
//     $wpMg = new \spamtonprof\stp_api\WpMg();
//     $wpMg->install_wp_new_domains($domains);
//     $wpMg->install_wp_new_domains($domains,"comparer");
    
    
    public function install_wp_new_domains($domains, $subdomain = false)
    {
        $cpanel = new \spamtonprof\stp_api\CpanelMg();

        // création des sous domaines
        // site modèle avec bhm désactivé
        // activé bhm sur les copies
        // ajouter widget avec les liens

        $sh = "";
        foreach ($domains as $domain) {

            $wd = "/home/yopla/" . $domain;
            if ($subdomain) {
                $wd = "/home/yopla/" . $subdomain . "." . $domain;
                $cpanel->add_sub_domain($subdomain, $domain);
                $domain = $subdomain . "." . $domain;
            }

            $this->clone_wp($domain, "suiviparemail.fr", "/home/yopla/www");

            $plugin_path = "/home/yopla/plugins/BHMCloaking-normal.zip";
            $this->install_plugins($wd, $plugin_path, "BHMCloaking");

            $plugin_path = "/home/yopla/plugins/wp-rocket.zip";
            $this->install_plugins($wd, $plugin_path, "wp-rocket");

            $this->create_bhm_campaigns($domain);

            $this->deactivate_plugin($wd, "wp-rocket");
            $this->activate_plugin($wd, "wp-rocket");

            $this->remove_cache($domain);

            $this->add_cron($domain);
        }

        $this->execute_remote($sh);

        echo (nl2br($sh));

        exit();
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

    
    public function search_replace($domain,$search,$replace)
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

        $email = 'alex.guillemine@gmail.com';
        $username = "yopla";

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

    public function execute_remote()
    {
        $sh1 = $this->sh;
        $sh1 = str_replace("cmdwp", "/usr/local/cpanel/3rdparty/bin/wp", $sh1);
        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/tempo.sh", $sh1);

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/execute_sh_via_ssh.sh");

        $sh = str_replace("[[file]]", "tempo.sh", $sh);
        $sh = str_replace("[[cmd]]", ". ~/sh/tempo.sh", $sh);

        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/execute_sh_via_ssh.sh", $sh);

        echo (nl2br($sh1));
    }
}