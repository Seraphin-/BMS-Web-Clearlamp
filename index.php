<?php
	require('canvas_data.php');
	require('create_table.php');
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:site" content="clearlamp.seraphin.xyz" />
		<meta name="twitter:title" content="BMS ClearLamp" />
		<meta name="twitter:description" content="<?php echo $tablename." ".strtoupper($mode)." LAMP"; if(!empty($player_name)) echo " (".$player_name.")";?>" />
		
		<script type="text/javascript" src="js/canvasjs.min.js"></script>
		<script type="text/javascript" src="js/classie.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="js/range.js"></script>
		<script type="text/javascript" src="js/jquery.stickybar.min.js"></script>
		
		<link href="style.css" rel="stylesheet" type="text/css">
        <link href="style-<?= (isset($_COOKIE['flash']) && $_COOKIE['flash'] == 1) ? "no-" : "" ?>flash.css" rel="stylesheet" type="text/css">

		<title><?php echo $tablename." ".strtoupper($mode)." LAMP"; if(!empty($player_name)) echo " (".$player_name.")";?></title>
		
		<style>
			.lamp_header {
				background-color: #ff5c5c;
			}
			.ha-header-front form select option{
				background-color: #ff5c5c;
			}
			#formbutton:hover{
				color: #ff5c5c;
			}
			#imageexport a:hover{
				color: #ff5c5c;
			}
			#modeselect input[type="radio"]:checked + label {
				background: white;
				color: #ff5c5c;
			}
			#modeselect label:hover {
				background: white;
				color: #ff5c5c;
			}
            optgroup {
                background-color: #ffb7b7;
            }
		</style>
	</head>
	
	<body>
		<header id="lamp_header" class="lamp_header">
			<div class="ha-header-front">
				
                <h1 id='tablename'><span><?=$tablename?> ClearLamp </span></h1>
				
				<?php
					if(!empty($playerstring))
						echo $playerstring;
					if($loaded_table) {
						echo '<div id="imageexport">
								<a class="shrinkbutton" id="download" href="#" download="'.$tablename." ".strtoupper($mode)." LAMP (Player:".$player_name.').png">Save as PNG</a>
							</div>';
					}
				?>
				
				<form name="LR2IDForm" method="GET" action="/">
					<button id="formbutton">OK</button>
					<div class="leftdiv">
						<label for="lr2ID">
							LR2ID:
						</label>
						<input type="text" name="lr2ID" id="lr2ID" pattern="[0-9]{0,6}" value="<?= (empty($lr2ID)===FALSE) ? $lr2ID : $_COOKIE['lr2ID']; ?>">
                        <label for="beatoraja_db">
                            Beatoraja DB Hash:
                        </label>
                        <input type="text" name="beatoraja_db" id="beatoraja_db" pattern="[a-f0-9]+" value="<?= (empty($beatoraja_db)===FALSE) ? $beatoraja_db : $_COOKIE['beatoraja_db']; ?>">
                        <a href="upload_db.php">(Upload DB)</a>
						<div id="modeselect" class="">
							<input type="radio" id="clear" name="mode" value="clear" checked <?php if(strcmp($mode, "clear") === 0) echo "checked"; ?>><label for="clear" class="toggle-btn">Clear</label>
							<input type="radio" id="judge" name="mode" value="judge" <?php if(strcmp($mode, "judge") === 0) echo "checked"; ?>><label for="judge" class="toggle-btn">Judgement</label>
						</div>
					</div>
					<div class="leftdiv">
						<label for="urlselect">	TABLE:</label>
						<select id="table_url" name="table_url" class="urlselect" onchange="this.form.submit()">
                            <?php
                            $tables = ['Select Table',
                                'Common Tables', 'normal1' => '☆ 通常難易度表', 'insane1' => '★ 発狂BMS難易度表', 'overjoy' => '★★ Overjoy', 'stellalite' => 'stl Stellalite', 'stellalitesub' => 'stl Stellalite Sub', 'satellite' => 'sl Satellite', 'stella' => 'st Stella',
                                'Specialized Tables', 'kuse1' => '!? 癖譜面コレクション(仮)', 'kuse2' => '¿¡ 癖譜面コレクション(サブ)', 'fox_table' => 'Σ：3 」 ∠ )ﾐ⌒ゞ', 'sara' => '◎ 通常皿難易度表', 'ln1' => '◆ LN難易度'];
                            $first = true;
                            foreach($tables as $val => $name) {
                                if (is_int($val)) {
                                    if(!$first) echo '</optgroup>';
                                    else $first = false;
                                    ?>
                                    <optgroup label="<?= $name ?>">
                                    <?php
                                } else {
                                    ?>
                                    <option value="<?= $val ?>" <?= ($table_url == $val) ? 'selected="yes"' : '' ?>><?= $name ?></option>
                                    <?php
                                }
                            }
                            ?>
						</select>
                        <button id="toggleflash">Toggle Flashing Lamps</button>
					</div>
				</form>
			</div>
		</header>
		<main class="wrapper">
			<div id="chartContainer" class="chartdiv"></div>
			
			<div id="bottomContainer">
				<div id="sidebar">
					<div id="filter">
						<div id="level-filter">
							<h3>LEVEL</h3>
							<div id="level-range" class="range filter-div" data-min="<?php echo min($level_int_arr); ?>" data-max="<?php echo max($level_int_arr); ?>" data-step="1">
								<input type="hidden" name="min-level" value="<?php echo min($level_int_arr);?>" />
								<input type="hidden" name="max-level" value="<?php echo max($level_int_arr);?>" />
								<div id="range-show"></div>
							</div>
							<div class="ck-button"><label>
								<input type="checkbox" name="char-level" value="charlv" checked/><span>+Char LV</span>
							</label></div>
						</div> 
						<div id="rank-filter" class="filter-div">
							<h3>RANK</h3>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="ALL-RANK" value="ALL-RANK" checked/><span>ALL</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="MAX" value="MAX" checked/><span>MAX</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="AAA" value="AAA" checked/><span>AAA</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="AA" value="AA" checked/><span>AA</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="A" value="A" checked/><span>A</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="B" value="B" checked/><span>B</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="C-F" value="C-F" checked/><span>C~F</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="rank-checkbox" type="checkbox" name="noplay" value="noplay" checked/><span>NO PLAY</span>
							</label></div>
						</div>
						<div id="clear-filter" class="filter-div">
							<h3>CLEAR</h3>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="ALL-CLEAR" value="ALL-CLEAR" checked/><span>ALL</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="FC" value="FULL-COMBO" checked/><span>FC</span>
							</label></div>
                            <? if (empty($beatoraja_db)===FALSE): ?>
                                <div class="ck-button"><label>
                                        <input class="clear-checkbox" type="checkbox" name="EX-HARD" value="EX-HARD" checked/><span>EX-HARD</span>
                                    </label></div>
                            <? endif; ?>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="HARD" value="HARD-CLEAR" checked/><span>HARD</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="CLEAR" value="CLEAR" checked/><span>CLEAR</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="EASY" value="EASY-CLEAR" checked/><span>EASY</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="FAILED" value="FAILED" checked/><span>FAILED</span>
							</label></div>
							<div class="ck-button"><label>
								<input class="clear-checkbox" type="checkbox" name="noplay" value="NOT-PLAYED" checked/><span>NO PLAY</span>
							</label></div>
						</div>
					</div>
				</div>
				
				<div id="tableContainer" class="tablediv">
					<?php
					//make table
					if(count($songdata) > 0) {
                        $clear_counter = array(0,0,0,0,0,0,0,0,0,0,0);
						$rank_counter = array(0,0,0,0,0,0);
						$table_string =  make_table($songdata, $clear_counter, $rank_counter);
						echo make_sum_table(empty($beatoraja_db)===FALSE, $clear_counter, $rank_counter);
						echo $table_string;
					}
					?>
				</div>
			</div>
			
			<!--
			<div id="twitbuttondiv">
				<a href="https://twitter.com/share" class="twitter-share-button" data-size="large">Tweet</a>
				<script>
				!function(d,s,id){
					var js,fjs=d.getElementsByTagName(s)[0], p=/^http:/.test(d.location)?'http':'https';
					if(!d.getElementById(id)) {
						js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';
						fjs.parentNode.insertBefore(js,fjs);
					}
				}(document, 'script', 'twitter-wjs');
				</script>
			</div>
			-->
		</main>
		<script>
			window.onload = function () {


				<?php
					if($mode === "clear")
                        if(empty($beatoraja_db)===FALSE) echo 'CanvasJS.addColorSet("pastel", ["#80FFFF", "#80FFFF", "#80FFFF", "#FFBF20", "#D9534F", "#17A2B8", "#40C000", "#FF80AA", "#FF80FF", "#606060", "#F0F0F0",]);';
						else echo 'CanvasJS.addColorSet("pastel", ["#FFC000", "#D9534F", "#FF8C00", "#40C000", "#606060", "#F0F0F0",]);';
					else
						echo 'CanvasJS.addColorSet("pastel", ["#CC0000", "#ffd040", "#BFC1C2", "#CD7F32", "#B0E57C", "#ACD1E9", "#F0F0F0"]);';
				?>
				
    			var chart = new CanvasJS.Chart("chartContainer", <?php echo $datafullstring;?>);
    			chart.render();
    			resizeh1();
				
				//tablesorter setting
				$.tablesorter.addParser({
						id: 'Clear',
						is: function(s) {
							return false;
						},
						format: function(s) {
							return s.replace(/NOT-PLAYED/,0).replace(/FAILED/,1).replace(/EASY-CLEAR/,2).replace(/HARD-CLEAR/,4).replace(/FULL-COMBO/,5).replace(/CLEAR/,3);
						},
						type: 'numeric'
				});	
				$.tablesorter.addParser({
					id: 'BP',
					is: function(s) {
						return false;
					},
					format: function(s) {
						return s.replace(/　/, 999999);
					},
					type: 'numeric'
				});
				$.tablesorter.addParser({
					id: 'LV',
					is: function(s) {
						return false;
					},
					format: function(s) {
						if(!($.isNumeric(s))) {
							return s.charCodeAt(0);
						}
						return s;
					},
					type: 'numeric'
				});
				$("#ScoreTable").tablesorter({
					headers: {
						0 : {sorter: false},
						1 : {sorter: 'LV'},
						4 : {sorter:'Clear'},
						6 : {sorter: 'BP'}
					}
				}); 
				
				range_show();
				
				//animate sidebar
				$('#sidebar').stickyBar({
					top: 50
				});

				$('#toggleflash').on('click', function(ev) {
				    ev.preventDefault();
				    let searchParams = new URLSearchParams(new URL(window.location).search);
				    searchParams.set('flash', <?= (isset($_COOKIE['flash']) && $_COOKIE['flash'] == 1) ? "true" : "false" ?>);
				    window.location.search = "?" + searchParams.toString();
                });

                window.setTimeout(imagefiledownload, 2000);
    		}
			
			//filter tds
			$("#filter").change(function() {
				$(".song-tr").show();
				
				var min_lv = parseInt($('input[name="min-level"]').attr('value'));
				var max_lv = parseInt($('input[name="max-level"]').attr('value'));
				var char_lv = $('input[name="char-level"]').prop("checked");
				if(max_lv < min_lv)
					[min_lv, max_lv] = [max_lv, min_lv];
				$(".level-td").each(function() {
					var currnum = $(this).html();
					var isnum = $.isNumeric(currnum);
					var currnum_int = parseInt(currnum);
					if((char_lv==false) && (isnum == false) ) {
						$(this).closest("tr").hide();
					} else if(isnum && (currnum_int < min_lv || currnum_int > max_lv) ) {
						$(this).closest("tr").hide();
					}
				})
				range_show();
				
				$('#rank-filter').find('input:not(:checked)').each(function() {
					var rank = $(this).attr('value');
					if(rank === "C-F"){
						$(".td-C").closest("tr").hide();
						$(".td-D").closest("tr").hide();
						$(".td-E").closest("tr").hide();
						$(".td-F").closest("tr").hide();
					} else {
						$(".td-".concat(rank)).closest("tr").hide();
					}
				});
			
				$('#clear-filter').find('input:not(:checked)').each(function() {
					var clear = $(this).attr('value');
					$(".".concat(clear)).closest("tr").hide();
				});
			});
			
			//checkbox all
			$("input[value='ALL-RANK']").change(function() {
				$(".rank-checkbox").prop("checked", $("input[value='ALL-RANK']").prop("checked"));
			});
			$("input[value='ALL-CLEAR']").change(function() {
				$(".clear-checkbox").prop("checked", $("input[value='ALL-CLEAR']").prop("checked"));
			});
			$(".rank-checkbox").change(function() {
				if($(this).attr("value") !== 'ALL-RANK')
					$("input[value='ALL-RANK']").prop("checked", false);				
			});
			$(".clear-checkbox").change(function() {
				if($(this).attr("value") !== 'ALL-CLEAR')
					$("input[value='ALL-CLEAR']").prop("checked", false);				
			});
			//checkbox noplay
			$("input[name='noplay']").change(function() {
				var no_check = $(this).prop("checked");
				$("input[name='noplay']").prop("checked", no_check);
			});
			
    		
			
    		window.onresize = function(event){
    			resizeh1();
    		}
    		
    		document.getElementById('download').addEventListener('onchange', function() {
			    imagefiledownload();
			}, false)
			
			//level range show
			function range_show() {
				$('#range-show').html(function() {
					var min = $('[data-name="min-level"]').attr('data-value');
					var max = $('[data-name="max-level"]').attr('data-value');
					if(parseInt(max) < parseInt(min))
						[min, max] = [max, min];
					return min.concat("~", max);
				});
			}
			
    		function imagefiledownload() {
    			document.getElementById('download').setAttribute("href", $('.canvasjs-chart-canvas')[0].toDataURL());
    		}
    		
    		function resizeh1() {
    			var winwidth = Math.max($(window).width(), 800);
    			var h2width = $('#playername').width();
    			$('#tablename').css({'width': (winwidth-h2width-250)+'px'});
    		};
    		
    		$('input[type=radio]').on('change', function() {
			    $(this).closest("form").submit();
			});
    		
			
			//animate header
			var cbpAnimatedHeader = (function() {	
				var docElem = document.documentElement,
					header = document.querySelector( '.lamp_header' ),
					button = document.querySelector( '.shrinkbutton')
					didScroll = false,
					changeHeaderOn = 10;
			
				function init() {
					window.addEventListener( 'scroll', function( event ) {
						if( !didScroll ) {
							didScroll = true;
							setTimeout( scrollPage, 250 );
						}
					}, false );
				}
			
				function scrollPage() {
					var sy = scrollY();
					if ( sy >= changeHeaderOn ) {
						classie.add( header, 'lamp_header-shrink' );
						classie.add( button, 'shrinkbutton-shrink');
					}
					else {
						classie.remove( header, 'lamp_header-shrink' );
						classie.remove( button, 'shrinkbutton-shrink');
					}
					didScroll = false;
				}
			
				function scrollY() {
					return window.pageYOffset || docElem.scrollTop;
				}
			
				init();
			})();
		</script>
	</body>
    <footer>
        Generated in <?= get_time() - $start ?> seconds. <a href="https://github.com/Seraphin-/BMS-Web-Clearlamp">Source Code</a>
    </footer>
</html>