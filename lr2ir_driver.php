<?php

class LR2IRDriver implements ScoreDriver{
    private $lr2_cgi;
    private $lr2ID;

    public function __construct($lr2id)
    {
        $this->lr2ID = $lr2id;
        $this->lr2_cgi = $this->get_lr2_cgi($lr2id);
        $this->scorexml = simplexml_load_string(substr($this->lr2_cgi, 1, strpos($this->lr2_cgi, rivalname)-2));

        if($this->scorexml === false) {
            exit("Unable to get score. <br><a href=\"javascript:history.go(-1)\">GO BACK</a>");
        }
    }

    public function get_playerstring($playername)
    {
        return "<h2 id='playername'>Player: <a target='_blank' href='http://www.dream-pro.info/~lavalse/LR2IR/search.cgi?mode=mypage&playerid=".$this->lr2ID."'>".$playername." (".$this->lr2ID.")"."</a> <i>(LR2IR)</i></h2>";
    }

    private function read_url($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    private function get_lr2_cgi()
    {
        return $this->read_url("http://www.dream-pro.info/~lavalse/LR2IR/2/getplayerxml.cgi?id=".preg_replace( "/[^0-9]/", "", $this->lr2ID));
    }

    function get_playername()
    {
        return mb_convert_encoding(substr($this->lr2_cgi, strpos($this->lr2_cgi, "<rivalname>")+11, strlen($this->lr2_cgi)-strpos($this->lr2_cgi, "<rivalname>")- 24), "UTF-8", "SJIS");
    }

    public function fetch_score(&$song)
    {
        foreach($this->scorexml->score as $score)
        {
            if(strcasecmp($score->hash, $song->{"md5"})===0)
            {
                $score->score = ((int)($score->pg))*2 + ((int)($score->gr));
                return $score;
            }
        }
        return false;
    }
}