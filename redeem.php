<?php

error_log("TEMPORARILY ALLOWING THIS PAGE TO BE ACCESSED BY GET");
//if ($_SERVER['REQUEST_METHOD'] != 'POST') die;	// really this shouldn't come from a GET

$inputs = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST : $_GET;

require_once('classes.php');
$iOSDevice = false;	// or set to a non-false text value
$userAgent = $_SERVER['HTTP_USER_AGENT'];
if (preg_match("/(\\(iPod|\\(iPhone|\\(iPad)/", $userAgent, $matches)) {
	$iOSDevice = substr($matches[1], 1);
}
$code = isset($inputs['code']) ? htmlspecialchars($inputs['code']) : '';
$email = isset($inputs['email']) ? htmlspecialchars($inputs['email']) : '';

$db = new DowncodeDB();

$album = $db->findAndRedeemAlbumFromCode($code, $iOSDevice);
$tracks = $db->tracksOfAlbumID($album['ID']);

?>
<html>
<head>
<style>

/*

NOW-PLAYING - NEED TO CONVERT TO A DIV WITH BACKGROUND IMAGE

*/

div#now-playing{
	display: inline-block;
	width: 40px;
	height: 40px;
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><circle fill="#00A0FF" cx="10" cy="10" r="10"/><path d="M8.9 12.13c-.1 0-.2-.03-.27-.09l-1.6-1.16H5.48a.47.47 0 0 1-.47-.46V8.85c0-.25.2-.46.47-.47h1.55l1.59-1.3a.5.5 0 0 1 .3-.1c.26 0 .47.2.47.47v4.22c0 .26-.21.46-.47.46zm1.87-.3l-.45-.44a2.8 2.8 0 0 0 0-3.51l.45-.44a3.42 3.42 0 0 1 0 4.4zm1.21 1.22l-.44-.44a4.5 4.5 0 0 0 0-5.95l.44-.44a5.13 5.13 0 0 1 0 6.83zm1.22 1.22l-.44-.44a6.22 6.22 0 0 0 0-8.39L13.2 5a6.85 6.85 0 0 1 0 9.27z" fill="#FFF"/></g></svg>');
}


#amplitude-left img[amplitude-song-info="cover_art_url"]{
	width: 400px;
	height: 400px;
}

#player-left-bottom{
	background-color: #F1F1F1;
	padding: 20px 10px;
}

span.current-time{
	color: #607D8B;
	font-size: 14px;
	font-weight: 700;
	float: left;
	width: 15%;
	text-align: center;
}

input[type=range].amplitude-song-slider{
	-webkit-appearance: none;
	width: 70%;
	float: left;
	margin-top: 10px;
}


input[type=range].amplitude-song-slider:focus {
	outline: none;
}

input[type=range].amplitude-song-slider::-webkit-slider-runnable-track {
	width: 75%;
	height: 1px;
	cursor: pointer;
	animate: 0.2s;
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider::-webkit-slider-thumb {
	height: 20px;
	width: 20px;
	border-radius: 10px;
	background: #00A0FF;
	cursor: pointer;
	margin-top: -8px;
	-webkit-appearance: none;
}

input[type=range].amplitude-song-slider:focus::-webkit-slider-runnable-track {
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider::-moz-range-track {
	width: 100%;
	height: 1px;
	cursor: pointer;
	animate: 0.2s;
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider::-moz-range-thumb {
	height: 20px;
	width: 20px;
	border-radius: 10px;
	background: #00A0FF;
	cursor: pointer;
	margin-top: -8px;
}

input[type=range].amplitude-song-slider::-ms-track {
	width: 100%;
	height: 1px;
	cursor: pointer;
	animate: 0.2s;
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider::-ms-fill-lower {
	background: #CFD8DC;
	border-radius: 2.6px;
}

input[type=range].amplitude-song-slider::-ms-fill-upper {
	background: #CFD8DC;
	border-radius: 2.6px;
}

input[type=range].amplitude-song-slider::-ms-thumb {
	height: 20px;
	width: 20px;
	border-radius: 10px;
	background: #00A0FF;
	cursor: pointer;
	margin-top: 4px;
}

input[type=range].amplitude-song-slider:focus::-ms-fill-lower {
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider:focus::-ms-fill-upper {
	background: #CFD8DC;
}

input[type=range].amplitude-song-slider::-ms-tooltip {
	display: none;
}

span.duration{
	color: #607D8B;
	font-size: 14px;
	font-weight: 700;
	float: left;
	width: 15%;
	text-align: center;
}

#time-container:after{
	content: "";
	display: table;
	clear: both;
}

#control-container{
	margin-top: 25px;
}
#repeat-container{
	width: 20%;
	float: left;
}

#repeat{
	width: 24px;
	height: 19px;
	cursor: pointer;
	float: right;
	margin-top: 20px;
}

#central-control-container{
	width: 60%;
	float: left;
}

#central-controls{
	width: 130px;
	margin: auto;
}

#previous{
	display: inline-block;
	width: 40px;
	height: 40px;
	cursor: pointer;
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="39" height="39" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><ellipse fill="#00A0FF" cx="19.5" cy="19.5" rx="19.5" ry="19.5"/><path d="M16.13 18.25V12.3c0-.72-.58-1.3-1.29-1.3H12.3a1.3 1.3 0 0 0-1.3 1.3v13.9c0 .72.58 1.3 1.29 1.3h2.55a1.3 1.3 0 0 0 1.3-1.3v-5.95l11.3 7c.85.56 1.55.15 1.55-.92V12.17c0-1.07-.7-1.48-1.56-.92l-11.3 7z" fill="#FFF"/></g></svg>');
	background-repeat: no-repeat;
	float: left;
	margin-top: 10px;
	margin-right: -5px;
}

#play-pause{
	display: inline-block;
	width: 60px;
	height: 60px;
	cursor: pointer;
	float: left;
}

#play-pause.amplitude-paused{
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><circle fill="#00A0FF" cx="30" cy="30" r="30"/><path d="M43.63 29.8L23.64 17.43c-1.46-.96-2.65-.26-2.65 1.56V43c0 1.81 1.19 2.52 2.65 1.56L43.63 32.2s.7-.5.7-1.2-.7-1.2-.7-1.2z" fill="#FFF"/></g></svg>');
}

#play-pause.amplitude-playing{
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><circle fill="#00A0FF" cx="30" cy="30" r="30"/><path d="M40.59 16.61h-4.33a2.2 2.2 0 0 0-2.2 2.2v23.6c0 1.21.99 2.2 2.2 2.2h4.33a2.2 2.2 0 0 0 2.18-2.2V18.8a2.18 2.18 0 0 0-2.18-2.2zm-16.39 0h-4.33a2.2 2.2 0 0 0-2.2 2.2v23.6c0 1.21 1 2.2 2.2 2.2h4.33a2.2 2.2 0 0 0 2.19-2.2V18.8a2.2 2.2 0 0 0-2.19-2.2z" fill="#FFF"/></g></svg>');
}

#next{
	display: inline-block;
	width: 40px;
	height: 40px;
	cursor: pointer;
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="39" height="39" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><ellipse fill="#00A0FF" cx="19.5" cy="19.5" rx="19.5" ry="19.5"/><path d="M27.68 11h-2.55a1.3 1.3 0 0 0-1.29 1.3v5.94l-11.28-6.99c-.86-.56-1.56-.15-1.56.92V26.3c0 1.07.7 1.48 1.56.92l11.28-7v5.95c0 .72.58 1.3 1.3 1.3h2.54c.71 0 1.28-.58 1.28-1.3V12.3c0-.72-.57-1.3-1.28-1.3z" fill="#FFF"/></g></svg>');
	background-repeat: no-repeat;
	float: left;
	margin-top: 10px;
	margin-left: -5px;
}

#shuffle-container{
	width: 20%;
	float: left;
}
#shuffle{
	width: 23px;
	height: 19px;
	cursor: pointer;
	float: left;
	margin-top: 20px;
}

#control-container:after{
	content: "";
	display: table;
	clear: both;
}

#meta-container{
	text-align: center;
	margin-top: 5px;
}

span.song-name{
	display: block;
	color: #272726;
	font-size: 20px;
	font-family: 'Open Sans', sans-serif;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

div.song-artist-album{
	color: #607D8B;
	font-size: 14px;
	font-weight: 700;
	text-transform: uppercase;
	font-family: 'Open Sans', sans-serif;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

div.song-artist-album span{
	display: block;
}

/* Player right styles */
#amplitude-right{
	padding: 0px;
	overflow-y: scroll;
}

div.song{
	cursor: pointer;
	padding: 10px;
}

div.song-now-playing-icon-container{
	float: left;
	width: 20px;
	height: 20px;
	margin-right: 10px;
}
img.now-playing{
	display: none;
	margin-top: 15px;
}

div.play-button-container{
	display: none;
	background: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"><path d="M7.24 5.04v11.78l9.82-5.6-9.82-6.18z" fill="#FFF"/><ellipse cx="11" cy="11" rx="10.5" ry="10.5" stroke="#FFF" stroke-linecap="square" fill="none"/></svg>');
	width: 22px;
	height: 22px;
	margin-top: 10px;
	opacity:50%;
}

div.play-button-container:hover{
	opacity:100%;
}

div.song.amplitude-active-song-container div.song-now-playing-icon-container img.now-playing{
	display: block;
}
div.song.amplitude-active-song-container div.song-now-playing-icon-container:hover img.now-playing div.play-button-container{
	display: none;
}
div.song-meta-data{
	float: left;
	width: calc( 100% - 110px );
}
span.song-title{
	color: #272726;
	font-size: 16px;
	display: block;
	font-weight: 300;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
span.song-artist{
	color: #607D8B;
	font-size: 14px;
	font-weight: bold;
	text-transform: uppercase;
	display: block;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

img.bandcamp-grey{
	float: left;
	display: block;
	margin-top: 10px;
}
img.bandcamp-white{
	float: left;
	display: none;
	margin-top: 10px;
}
span.song-duration{
	float: left;
	width: 55px;
	text-align: center;
	line-height: 45px;
	color: #607D8B;
	font-size: 16px;
	font-weight: 500;
}

div.song:after{
	content: "";
	display: table;
	clear: both;
}

/* Small only */
@media screen and (max-width: 39.9375em) {
	#amplitude-left img[amplitude-song-info="cover_art_url"]{
		width: auto;
		height: auto;
	}
}


</style>
</head>
<body>

<?php if (!$album) { ?>
<p>
Sorry, but the code you used is not valid, or has already been redeemed. Please double-check the code and try again. If you are sure this is an error, please contact webmaster@lorenzowoodmusic.com with the code that you used.
</p>
<?php die; } ?>




<div id="redeem_console">
	<h1><?php echo htmlspecialchars($album['title']); ?></h1>
	<p><?php echo htmlspecialchars($album['description']); ?></p>
	<form id="downloader">
		<input type="hidden" id="a_input" name="a" value="<?php echo $album['ID']; ?>" />
		<input type="hidden" id="c_input" name="c" value="<?php echo $code; ?>" />

<?php
if (!$iOSDevice) {
	echo '<h2>Format:</h2>' . PHP_EOL;
	echo '<select id="f_input" name="f">' . PHP_EOL;
	$formats = $db->formats();
	foreach ($formats as $format) {
		echo '<option value="' . $format['ID'] . '"';
		echo ' data-extension="' . $format['extension'] . '"';	// not really used, but maybe a script could populate something?
		if (isset($album['formatID']) && $album['formatID'] == $format['ID']) {
			echo ' data-previous-format="' . $album['formatID'] . '" selected';
		}
		else if (!isset($album['formatID'])
			&& isset($format['platform_preg'])
			&& preg_match($format['platform_preg'], $userAgent, $matches)) {
			echo ' data-platform="' . $matches[0] . '" selected';
		}
		echo '>' . htmlspecialchars($format['description']) . '</option>' . PHP_EOL;
	}
	echo '</select>' . PHP_EOL;
}
?>

<!-- Player Console -->

		<div class="row">
			<div class="" id="">
				<div class="row">
					<div class="" id="amplitude-left">
						<img amplitude-song-info="cover_art_url" amplitude-main-song-info="true"/>
						<div id="player-left-bottom">
							<div id="time-container">
								<span class="current-time">
									<span class="amplitude-current-minutes" amplitude-main-current-minutes="true"></span>:<span class="amplitude-current-seconds" amplitude-main-current-seconds="true"></span>
								</span>
								<input type="range" class="amplitude-song-slider" amplitude-main-song-slider="true" step=".1"/>
								<span class="duration">
									<span class="amplitude-duration-minutes" amplitude-main-duration-minutes="true"></span>:<span class="amplitude-duration-seconds" amplitude-main-duration-seconds="true"></span>
								</span>
							</div>

							<div id="control-container">
								<div id="repeat-container">
									<div class="amplitude-repeat" id="repeat"></div>
								</div>

								<div id="central-control-container">
									<div id="central-controls">
										<div class="amplitude-prev" id="previous"></div>
										<div class="amplitude-play-pause" amplitude-main-play-pause="true" id="play-pause"></div>
										<div class="amplitude-next" id="next"></div>
									</div>
								</div>

								<div id="shuffle-container">
									<div class="amplitude-shuffle amplitude-shuffle-off" id="shuffle"></div>
								</div>
							</div>

							<div id="meta-container">
								<span amplitude-song-info="name" amplitude-main-song-info="true" class="song-name"></span>

								<div class="song-artist-album">
									<span amplitude-song-info="artist" amplitude-main-song-info="true"></span>
									<span amplitude-song-info="album" amplitude-main-song-info="true"></span>
								</div>
							</div>
						</div>
					</div>
					<div id="amplitude-right">

<?php
if (!$iOSDevice) {
?>
						<div><button type="button" class="download" name="t" value="0">Download all</button></div>
<?php
}

foreach ($tracks as $track) {
?>
						<div class="song amplitude-song-container amplitude-play-pause" amplitude-song-index="<?php echo $track['trackNumber']; ?>">
							<div class="song-now-playing-icon-container">
								<div class="play-button-container">

								</div>
								<div class="now-playing"></div>
							</div>
							<div class="song-meta-data">
								<span class="song-title"><?php echo htmlspecialchars($track['title']); ?></span>
							</div>
							<span class="song-duration">DUR</span>
<?php
	if (!$iOSDevice) {
?>
							<div class="song-download">
								<button type="button" class="download" name="t" value="<?php echo $track['ID']; ?>">Download</button>
								<?php echo htmlspecialchars($track['fileBase']); ?>
							</div>
<?php
	}
?>

						</div>
<?php
}
?>


					</div>
				</div>
			</div>
		</div>
	</form>
</div> <!-- redeem_console -->





<!-- jquery already loaded ??? -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<script>
$('button.download').click(function (evt) {
	evt.preventDefault();

	var button = $(evt.target);
	// Build a form
	// Based on this: https://gist.github.com/DavidMah/3533415
	var form = $('<form>').attr('action', 'download.php').attr('method', 'post');
	form.append($("<input>").attr('type', 'hidden').attr('name', 't').attr('value', button.attr('value')));
	form.append($("<input>").attr('type', 'hidden').attr('name', 'a').attr('value', $('#a_input').val()));
	form.append($("<input>").attr('type', 'hidden').attr('name', 'c').attr('value', $('#c_input').val()));
	form.append($("<input>").attr('type', 'hidden').attr('name', 'f').attr('value', $('#f_input').val()));
	form.appendTo('body').submit().remove();

	// Disable the downloading button
	button.prop("disabled",true);
	// Dim the download console for just a second so we know that something is happening when we click download. Good or dumb idea?
	$("#redeem_console").parent().animate({
		opacity: 0.5,
	 	}, 100, function() {
		setTimeout(function(){
			$('#redeem_console').parent().css({"opacity":"1.0"});
			button.prop("disabled",false); // and restore the button
		}, 900);
	});
});

</script>
<script src="https://cdn.jsdelivr.net/npm/amplitudejs@3.2.3/dist/amplitude.js"></script>
<?php error_log("Should include minimized amplitudejs"); ?>
<script>
Amplitude.init({
	"songs": [
<?php
foreach ($tracks as $track) {
?>
		{
			"name": "<?php echo htmlspecialchars($track['title']); ?>",
			"album": "<?php echo htmlspecialchars($album['title']); ?>",
			"url": "tracks/<?php echo htmlspecialchars($track['fileBase']); ?>.mp3",
		},
<?php
}
?>
	],
	"default_album_art": "albums/<?php echo htmlspecialchars($album['imageName']); ?>"
});
</script>
</body>
</html>
<?php
$db->close();
?>