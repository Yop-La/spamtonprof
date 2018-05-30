<?php
namespace spamtonprof\slack;

/**
 *
 * @author alexg
 *         pour communiquer avec slack
 */
class Slack

{

    const LogLbc = "log-lbc", Log = "log", Abonnement = "abonnement", Invoicing = "invoicing";

    private $slack;

    public function __construct()
    {
        $this->slack = new \wrapi\slack\slack(SLACK_TOKEN);
    }

    public function sendMessages(string $channel, array $msgs)
    {
        $sum = array_map("strlen", $msgs);
        
        $sum = array_sum($sum);
        
        $nbSplits = ceil($sum / 20000);
        
      
        if ($nbSplits > 1) {
            $array_msgs = array_chunk($msgs, $nbSplits);
        } else {
            $array_msgs = array($msgs);
        }
        
        $res = [];
        
        foreach ($array_msgs as $msgs) {
            
            $response = $this->slack->chat->postMessage(array(
                "channel" => "#" . $channel,
                "text" => implode("\n", toUtf8($msgs)),
                "username" => "Stp Bot",
                "as_user" => false,
                "parse" => "full",
                "link_names" => 1,
                "unfurl_links" => true,
                "unfurl_media" => false
            ));
            
            $res[] = $response;
        }
        
        return ($res);
    }
}