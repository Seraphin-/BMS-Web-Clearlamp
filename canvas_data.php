<?php
interface ScoreDriver {
    public function __construct($parameter);
    public function get_playername();
    public function fetch_score(&$song);
    public function get_playerstring($playername);
}

require_once 'lr2ir_driver.php';
require_once 'beatoraja_driver.php';

function add_song($song, $mode, $level, $i)
{
	if($mode === "clear") {
		if($i !== 0)
		{
			if(strcmp($song->{"level"}, $level) === 0 && strcmp($song->{"clear"}, $i) === 0)
				return true;
		} 
		else
		{
			if(strcmp($song->{"level"}, $level) === 0 && empty($song->{"clear"})){
				return true;
			}
		}
	} else {
        if(!isset($song->notes)) return false;
		if(strcmp($song->{"level"}, $level) === 0)
		{
			switch($i) {
				case 6:
					if($song->{"notes"}*2 === $song->{"score"}) {
						return true;
					}
					break;
				case 5:
					if($song->{"notes"}*2*0.8889 <= $song->{"score"} && $song->{"notes"}*2 > $song->{"score"}) {
						return true;
					}
					break;
				case 4:
					if($song->{"notes"}*2*0.7778 <= $song->{"score"} && $song->{"notes"}*2*0.8889 > $song->{"score"}) {
						return true;
					}
					break;
				case 3:
					if($song->{"notes"}*2*0.6667 <= $song->{"score"} && $song->{"notes"}*2*0.7778 > $song->{"score"}) {
						return true;
					}
					break;
				case 2:
					if($song->{"notes"}*2*0.5556 <= $song->{"score"} && $song->{"notes"}*2*0.6667 > $song->{"score"}) {
						return true;
					}
					break;
				case 1:
					if($song->{"notes"}*2*0.5556 > $song->{"score"} && $song->{"score"} > 0) {
						return true;
					}
					break;
				case 0:
					if(empty($song->{"clear"})) {
						return true;
					}
					break;
			}
		}
	}
	return false;
}

//Tooltip용 링크데이터
function tooltip_string($cleararray) 
{
	$linkdata = "<ul class='song_list'>";
	foreach($cleararray as $clearedsong){
		$linkdata=$linkdata.
		"<li><a target='_blank' href='http://www.dream-pro.info/~lavalse/LR2IR/search.cgi?mode=ranking&bmsmd5=".$clearedsong->{"md5"}."'>".str_replace("\"", "", $clearedsong->{"title"})."</a></li>";
	}
	$linkdata = $linkdata."</ul>";
	return $linkdata;
}

function get_currentclear($mode, $i, &$indexlabelcolor)
{
	if($mode === "clear") {
		switch ($i) {
            case 10:
                $currentclear = "MAX";
                break;
            case 9:
                $currentclear = "PERFECT";
                break;
            case 8:
                $currentclear = "EX-HARD";
                break;
            case 7:
                $currentclear = "LIGHT ASSIST EASY";
                break;
            case 6:
                $currentclear = "ASSIST EASY";
                break;
			case 5:
				$currentclear = "FC";
				$indexlabelcolor = "#171717";
				break;
			case 4:
				$currentclear = "HARD";
				break;
			case 3:
				$currentclear = "CLEAR";
				break;
			case 2:
				$currentclear = "EASY";
				break;
			case 1:
				$currentclear = "FAILED";
				break;
			case 0:
				$currentclear = "Not Played";
				$indexlabelcolor = "#171717";
				break;
			default:
				break;
		}
	} else {
		switch ($i) {
			case 6:
				$currentclear = "MAX";
				break;
			case 5:
				$currentclear = "AAA";
				$indexlabelcolor = "#171717";
				break;
			case 4:
				$currentclear = "AA";
				$indexlabelcolor = "#171717";
				break;
			case 3:
				$currentclear = "A";
				break;
			case 2:
				$currentclear = "B";
				break;
			case 1:
				$currentclear = "C~F";
				$indexlabelcolor = "#171717";
				break;
			case 0:
				$currentclear = "Not Played";
				$indexlabelcolor = "#171717";
				break;
			default:
				break;
		}
	}
	return $currentclear;
}
function get_time() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$start = get_time();

$table_url = preg_replace( "/[^a-z0-9_]/", "", isset($_GET["table_url"]) ? $_GET["table_url"] : "");
$tablename = "(No table loaded)";
$lr2ID = isset($_GET["lr2ID"]) ? $_GET["lr2ID"] : "";
$mode = isset($_GET["mode"]) ? $_GET["mode"] : "";
$beatoraja_db  = preg_replace( "/[^a-f0-9_]/", "", isset($_GET["beatoraja_db"]) ? $_GET["beatoraja_db"] : "");;
if(empty($beatoraja_db)===FALSE) setcookie("beatoraja_db", $beatoraja_db, 0, "/", $_SERVER['SERVER_NAME'], true, true);
if(empty($lr2ID)===FALSE) setcookie("lr2ID", $lr2ID, 0, "/", $_SERVER['SERVER_NAME'], true, true);
$playerstring = "";
if(empty($_GET["mode"]))
{
	$mode = 'clear';
}

$loaded_table = false;

if(empty($lr2ID)===FALSE && empty($beatoraja_db)===FALSE) exit("Please only specify either LR2 ID or Beatoraja DB Hash <br><a href=\"javascript:history.go(-1)\">GO BACK</a>");
if((empty($lr2ID)===FALSE || empty($beatoraja_db)===FALSE) && empty($table_url)===FALSE) {
    if(!file_exists('tables/'.$table_url.'.json')) exit("Unable to open Table <br><a href=\"javascript:history.go(-1)\">GO BACK</a>");
    /** @var ScoreDriver $driver */
    if(empty($lr2ID)==FALSE) $driver = new LR2IRDriver($lr2ID);
	else {
	    if(!file_exists('dbs/'.$beatoraja_db)) exit("Unable to open DB <br><a href=\"javascript:history.go(-1)\">GO BACK</a>");;
	    $driver = new BeatorajaDriver($beatoraja_db);
    }

	$player_name = $driver->get_playername();
	$playerstring = $driver->get_playerstring($player_name);

	// We are just going to read the json from disk, since normal tables don't include the sha256 anyway.
    // We can't support custom table URLs this way but it's fine.
	$json = json_decode($datastring = file_get_contents('tables/'.$table_url.'.json'));
    $tablename = $json->name;
    $tablesymbol = $json->symbol;
    $songdata = $json->songdata;
    
	//Level의 목록을 get하고 clear항목을 cgi와 대조해 추가
	$levelarr = array();
	$all_level_count = array(0,0,0,0,0,0,0,0,0,0,0,0);
    foreach($songdata as $song)
    {
        $score = $driver->fetch_score($song);
        if($score) {
            $song->{"clear"} = $score->clear;
            $all_level_count[(int)$score->clear]++;
            $song->{"score"} = $score->score;
            $song->{"notes"} = (int)($score->notes);
            $song->{"minbp"} = (int)($score->minbp);
        }
        if(!in_array($song->{"level"}, $levelarr))
            $levelarr[] = $song->{"level"};
    }
//	natsort($levelarr);
	$levelarr = array_reverse($levelarr);

	$level_int_arr = array_filter($levelarr, "is_numeric");
    
	//canvajs용 데이터 만들기
	$datafullstring = "";

	if($mode === "clear")
        if(empty($beatoraja_db)==FALSE) $clear_order = [10,9,5,8,4,3,2,7,6,1,0];
        else $clear_order = [5,4,3,2,1,0];
    else $clear_order = [6,5,4,3,2,1,0];
	foreach($clear_order as $i)
	{
		$indexlabelcolor = "white";
		$currentclear = get_currentclear($mode, $i, $indexlabelcolor);

		//level 갯수에 따라 폰트사이즈 조정
		$indexfontsize = 22;
		$label_fontsize = 24;
		if(count($levelarr) < 16){
			$indexfontsize = 30;
			$label_fontsize = 30;
		} else if(count($levelarr) > 30){
			$label_fontsize = 17;
		}
		
		$datastring = "
		{
			indexLabelFontSize: ".$indexfontsize.",
			type: 'stackedBar100',
			showInLegend: true,
			toolTipContent: \"<span class='tooltip_h'>{label} {name} ({count})</span><hr/>{linkdata}\",
			name: '".$currentclear."',
			dataPoints:
			[
				";
		
		$all_level_counter = 0;

		foreach($levelarr as $level)
		{
			$cleararray = array();
			
			//songdata를 돌면서 현재 클리어(i)상태인 곡들 cleararray에 추가
			foreach($songdata as $song) {
				if(add_song($song, $mode, $level, $i)) 
				{
					$cleararray[] = $song;
				}
			}

			$currlevel_counter = count($cleararray);
			$all_level_counter +=$currlevel_counter;

			

			if($currlevel_counter > 0)
			{
				$linkdata = tooltip_string($cleararray);

				$datastring = $datastring."{y: ".$currlevel_counter.",
				linkdata: \"".$linkdata."\",
				count: '".$currlevel_counter."',
				label: '".$tablesymbol.$level."',
				indexLabelFontColor: '".$indexlabelcolor."',
				indexLabel: '".$currlevel_counter."',
				indexLabelPlacement: 'inside',
				},";
			}
			else 
			{
				$datastring = $datastring."{y: ".$currlevel_counter.",
				label: \"".$tablesymbol.$level."\"},";
			}
		}

		/* 
		//all level 추가
		if($all_level_counter>0)
		{
			$all_level_count[$i] = $all_level_counter;
			$datastring = $datastring."{y: ".$all_level_counter.",
			linkdata: \"\",
			count: \"".$all_level_counter."\",
			label: \"".$tablesymbol."All"."\",
			indexLabelFontColor: \"".$indexlabelcolor."\",
			indexLabel: \"".$all_level_counter."\",
			indexLabelPlacement: \"inside\",
			},";
		}
		else{
			$datastring = $datastring."{y: ".$all_level_counter.",
			label: \"".$tablesymbol."All"."\"},";
		}
		*/

		//끝부분 정리
		$datastring = substr($datastring, 0, strlen($datastring)-1)."]},";
		$datafullstring .=$datastring;
	}
	
	$all_level_count[0] = count($songdata) - $all_level_count[5] - $all_level_count[4] - $all_level_count[3] - $all_level_count[2] - $all_level_count[1]; 
	
	$datafullstring = "
    {
    	title: {
    		text: \"".$tablename." ".strtoupper($mode)." LAMP (Player: ".$player_name.")\",
    		horizontalAlign: 'left',
    		fontSize: 25,
    		fontFamily: \"arial\",
    	},
    	backgroundColor: 'white',
    	animationEnabled: true,
		animationDuration: 1500,
    	toolTip: {
	      	shared: false,
	      	borderColor: \"black\",
	      	fontSize: 25,
	    },
	    legend:{
	    	fontSize: 20,
	    	fontFamily: \"arial\",
	    	verticalAlign: \"top\",
	    	horizontalAlign: \"right\",
		 },
    	colorSet: \"pastel\",
    	axisX:{
    		interval: 1,
    		labelFontSize: ".$label_fontsize.",
    	},
    	axisY:{
    		interval: 100,
    		labelFontColor: \"white\",
    	},
    	data:[".substr($datafullstring, 0, strlen($datafullstring)-1)."]}";
	$loaded_table = true;
}

?>