<?php
namespace spamtonprof\stp_api;

class WpMg

{

    public function __construct()

    {
        $script = "";
    }
    
    public function update_links_on_network(){
        
        $target_subdomains = [
            "comparateurs",
            "comparer",
            "conseils",
            "cabinet",
            ""
        ];
        
        $domains = [
            "acmbasket.com",
            "ateliers-broderie-doyen.com",
            "balade-des-vignerons.fr",
            "basketpaysgex.fr",
            "gerda2017.com",
            "gite-haut-allier.fr",
            "labayonnaise04.com",
            "mendespokerclub.fr",
            "recuperer-son-ex.info",
            "se-marier-en-caleche.com",
            "web-annuaire-france.com"
        ];
        $sh = "";
        foreach ($target_subdomains as $target_subdomain){
            
            $sh = $sh .  $this->update_links_on_subdomains($domains,$target_subdomain,$target_subdomains);
            
        }
        
        $this->execute_remote($sh);
        
        echo (nl2br($sh));
        
    }

    public function update_links_on_subdomains($domains,$target_sub,$target_subdomains)
    {


        $pre_subdomains = $target_subdomains;


        $wpmg = new \spamtonprof\stp_api\WpMg();
        // $wpmg -> clone_wp($domain, $domain_to_clone, $path_to_clone);
        $sh = "";
        foreach ($domains as $domain) {

            $wd = "/home/aafhpget/" . $target_sub . "." . $domain;
            
            if ($target_sub == "") {
                $wd = "/home/aafhpget/sites/"  . explode(".", $domain)[0];
            }

            if (($key = array_search($target_sub, $pre_subdomains)) !== false) {
                unset($pre_subdomains[$key]);
            }

            $sh = $sh . $wpmg->add_subdomains_link($wd, $domain, $pre_subdomains);

            $sh = $sh . $wpmg->remove_cache($wd);

         
        }

        return($sh);
        
    }

    public function install_wp_new_subdomains()
    {
        
        $cpanel = new \spamtonprof\stp_api\CpanelMg();

        // création des sous domaines
        // site modèle avec bhm désactivé
        // activé bhm sur les copies
        // ajouter widget avec les liens

        $domains = [
            "aafhaiti.org",
            "acmbasket.com",
            "ateliers-broderie-doyen.com",
            "balade-des-vignerons.fr",
            "basketpaysgex.fr",
            "gerda2017.com",
            "gite-haut-allier.fr",
            "labayonnaise04.com",
            "mendespokerclub.fr",
            "recuperer-son-ex.info",
            "se-marier-en-caleche.com",
            "web-annuaire-france.com"
        ];

        $subdomain = "comparer";

        $wpmg = new \spamtonprof\stp_api\WpMg();

        $sh = "";
        foreach ($domains as $domain) {

            $wd = "/home/aafhpget/" . $subdomain . "." . $domain;

            
            $cpanel->add_sub_domain($subdomain, $domain);

            $sh = $sh . $wpmg->clone_wp("$subdomain." . $domain, "essai.aafhaiti.org", "/home/aafhpget/essai.aafhaiti.org");

            $plugin_path = "/home/aafhpget/plugins/BHMCloaking-normal.zip";
            $sh = $sh . $wpmg->install_plugins($wd, $plugin_path, "BHMCloaking");

            $plugin_path = "/home/aafhpget/plugins/wp-rocket.zip";
            $sh = $sh . $wpmg->install_plugins($wd, $plugin_path, "wp-rocket");

            $sh = $sh . $wpmg->create_bhm_campaigns($wd);

            $sh = $sh . $wpmg->deactivate_plugin($wd, "wp-rocket");
            $sh = $sh . $wpmg->activate_plugin($wd, "wp-rocket");
    
            $sh = $sh . $wpmg->remove_cache($wd);

            $sh = $sh . $wpmg->add_cron("$subdomain." . $domain);
        }

        $wpmg->execute_remote($sh);

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

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function create_bhm_campaigns($dir)
    {
        $params = array(
            'dir' => $dir,
            'minutes' => 1,
            'maillage' => 4,
            'nb_posts' => 2000
        );

        $campaigns = [];
        // pinel
        $campaigns[] = array(
            'cat_id' => 3,
            'url' => 'https://generator.blackhat.money/page/3/775?token=50637a15e23bf111801a8138e430174f'
        );
        $campaigns[] = array(
            'cat_id' => 3,
            'url' => 'https://generator.blackhat.money/page/401/775?token=50637a15e23bf111801a8138e430174f'
        );

        // rachat crédit
        $campaigns[] = array(
            'cat_id' => 4,
            'url' => 'https://generator.blackhat.money/page/3/776?token=50637a15e23bf111801a8138e430174f'
        );
        $campaigns[] = array(
            'cat_id' => 4,
            'url' => 'https://generator.blackhat.money/page/401/776?token=50637a15e23bf111801a8138e430174f'
        );

        // crédit immo
        $campaigns[] = array(
            'cat_id' => 5,
            'url' => 'https://generator.blackhat.money/page/3/777?token=50637a15e23bf111801a8138e430174f'
        );
        $campaigns[] = array(
            'cat_id' => 5,
            'url' => 'https://generator.blackhat.money/page/401/777?token=50637a15e23bf111801a8138e430174f'
        );

        // lmnp
        $campaigns[] = array(
            'cat_id' => 6,
            'url' => 'https://generator.blackhat.money/page/3/778?token=50637a15e23bf111801a8138e430174f'
        );
        $campaigns[] = array(
            'cat_id' => 6,
            'url' => 'https://generator.blackhat.money/page/401/778?token=50637a15e23bf111801a8138e430174f'
        );

        // mutuelle santé
        $campaigns[] = array(
            'cat_id' => 7,
            'url' => 'https://generator.blackhat.money/page/3/791?token=50637a15e23bf111801a8138e430174f'
        );
        $campaigns[] = array(
            'cat_id' => 7,
            'url' => 'https://generator.blackhat.money/page/401/791?token=50637a15e23bf111801a8138e430174f'
        );

        $sh = "";
        foreach ($campaigns as $campaign) {
            $params['cat_id'] = $campaign['cat_id'];
            $params['url'] = $campaign['url'];

            $sh = $sh . $this->add_bhm_campaign($params);
        }

        return ($sh);
    }

    public function remove_cache($wd)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/clear_cache.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function install_wprocket_cli($wd)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/install_wp_rocket_cli.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function deactivate_plugin($wd, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/deactivate_plugin.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function add_cron($domain)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/add_cron.sh");

        $sh = str_replace("[[domain]]", $domain, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function activate_plugin($wd, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/activate_plugin.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function install_plugins($wd, $plugin_path, $plugin_name)
    {
        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/install_plugins.sh");

        $sh = str_replace("[[dir]]", $wd, $sh);
        $sh = str_replace("[[plugin_name]]", $plugin_name, $sh);
        $sh = str_replace("[[plugin_path]]", $plugin_path, $sh);

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

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

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
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
            $pathtoinstall = "/home/aafhpget/$domain";
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

        $sh = str_replace("cmdwp", "~/wd/scripts/wp", $sh);

        return ($sh);
    }

    public function execute_remote($sh)
    {
        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/tempo.sh", $sh);

        $sh = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/template/execute_sh_via_ssh.sh");

//         $sh = str_replace("[[file]]", "tempo.sh", $sh);
//         $sh = str_replace("[[cmd]]", ". ~/sh/tempo.sh", $sh);

        file_put_contents(ABSPATH . "wp-content/plugins/spamtonprof/sh/run/execute_sh_via_ssh.sh", $sh);
    }
}