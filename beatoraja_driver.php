<?php
class BeatorajaDriver implements ScoreDriver
{
    private $hash;
    private $pdo;

    public function __construct($hash)
    {
        $this->hash = $hash;
        $this->pdo = new PDO('sqlite:' . 'dbs/' . $hash);
    }

    public function fetch_score(&$song)
    {
        $matched = $this->pdo->query('SELECT * FROM score WHERE sha256 == "' . $song->sha256 . '"', PDO::FETCH_ASSOC)->fetch(); // No need to prepare, we trust ourselves (I hope)
        if($matched) {
            $matched = (object)$matched;
            $matched->score = ((int)($matched->epg) + (int)($matched->lpg)) * 2 + ((int)($matched->egr) + (int)($matched->lgr));
            switch ($matched->clear) {
                case 8:
                    $matched->clear = 5;
                    break;
                case 7:
                    $matched->clear = 8;
                    break;
                case 6:
                case 5:
                case 4:
                    $matched->clear -= 2;
                    break;
                case 3:
                    $matched->clear = 7;
                    break;
                case 2:
                    $matched->clear = 6;
                default:
                    break;
            }
        }
        return $matched;
    }

    public function get_playername()
    {
        return $this->hash; // Beatoraja DB doesn't include the player name...
    }
    public function get_playerstring($playername)
    {
        return "<h2 id='playername'>Player hash: " . $this->hash . " <i>(beatoraja DB)</i></h2>";
    }
}