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
<!DOCTYPE html>
<html>
<head>
<link href="styles.css" rel="stylesheet" />
<style>

body { font-family:'Gill Sans';}

div#now-playing{
	display: inline-block;
	width: 20px;
	height: 20px;
	background-color:red;
	background: url('data:image/svg+xml;charset=UTF-8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><circle fill="#00A0FF" cx="10" cy="10" r="10"/><path d="M8.9 12.13c-.1 0-.2-.03-.27-.09l-1.6-1.16H5.48a.47.47 0 0 1-.47-.46V8.85c0-.25.2-.46.47-.47h1.55l1.59-1.3a.5.5 0 0 1 .3-.1c.26 0 .47.2.47.47v4.22c0 .26-.21.46-.47.46zm1.87-.3l-.45-.44a2.8 2.8 0 0 0 0-3.51l.45-.44a3.42 3.42 0 0 1 0 4.4zm1.21 1.22l-.44-.44a4.5 4.5 0 0 0 0-5.95l.44-.44a5.13 5.13 0 0 1 0 6.83zm1.22 1.22l-.44-.44a6.22 6.22 0 0 0 0-8.39L13.2 5a6.85 6.85 0 0 1 0 9.27z" fill="#FFF"/></g></svg>');
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

div.song.amplitude-active-song-container div.song-now-playing-icon-container div.now-playing{
	display: block;
}
div.song.amplitude-active-song-container div.song-now-playing-icon-container:hover div.now-playing div.play-button-container{
	display: none;
}
div.song-meta-data{
	float: left;
	width: calc( 100% - 200px );
}
div.song-download {
	float:left;
	width:100px;
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

span.song-duration, span.track-number{
	float: left;
	width: 55px;
	text-align: center;
	color: #607D8B;
	font-size: 16px;
	font-weight: 500;
}

div.song:after{
	content: "";
	display: table;
	clear: both;
}

/* Responsive, big containers */

#cover-art-container, #time-container, #central-controls, #meta-container, #list-container {
	width:50%;
	float:left;
}
#time-container, #central-controls, #meta-container{
	background:#f1f1f1;
}

/* Small only */
@media screen and (max-width: 39.9375em) {

	#cover-art-container, #time-container, #central-controls, #meta-container, #list-container {
		width:100%;
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

<div id="cover-art-container">
<img amplitude-song-info="cover_art_url" amplitude-main-song-info="true"/>
</div>

<div id="time-container">
	<span class="current-time">
		<span class="amplitude-current-minutes" amplitude-main-current-minutes="true"></span>:<span class="amplitude-current-seconds" amplitude-main-current-seconds="true"></span>
	</span>
	<input type="range" class="amplitude-song-slider" amplitude-main-song-slider="true" step=".1"/>
	<span class="duration">
		<span class="amplitude-duration-minutes" amplitude-main-duration-minutes="true"></span>:<span class="amplitude-duration-seconds" amplitude-main-duration-seconds="true"></span>
	</span>
</div>


<div id="central-controls">
	<div id="central-controls-grouped">
		<div class="amplitude-prev" id="previous"></div>
		<div class="amplitude-play-pause" amplitude-main-play-pause="true" id="play-pause"></div>
		<div class="amplitude-next" id="next"></div>
	</div>
</div>

<div id="meta-container">
	<span amplitude-song-info="name" amplitude-main-song-info="true" class="song-name"></span>

	<div class="song-artist-album">
		<span amplitude-song-info="artist" amplitude-main-song-info="true"></span>
		<span amplitude-song-info="album" amplitude-main-song-info="true"></span>
	</div>
</div>

<div id="list-container">

<?php
if (!$iOSDevice) {
?>
	<div><button type="button" class="download" name="t" value="0">Download all</button></div>
<?php
}

$trackIndex = 0;
foreach ($tracks as $track) {
?>
	<div class="song amplitude-song-container amplitude-play-pause" amplitude-song-index="<?php echo $trackIndex++; ?>">
		<div class="song-now-playing-icon-container">
			<div class="play-button-container">

			</div>
			<div class="now-playing"></div>
		</div>
		<div class="song-meta-data">
			<span class="track-number"><?php echo htmlspecialchars($track['trackNumber']); ?>.</span>
			<span class="song-title"><?php echo htmlspecialchars($track['title']); ?></span>
		</div>
		<span class="song-duration"><?php echo htmlspecialchars($track['duration']); ?></span>
<?php
if (!$iOSDevice) {
?>
		<div class="song-download">
			<button type="button" class="download" name="t" value="<?php echo $track['ID']; ?>">Download</button>
		</div>
<?php
	}
?>
	</div>
<?php
}
?>
</div> <!-- list-container -->

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
			"artist": "<?php echo htmlspecialchars($album['artist']); ?>",
			"url": "downcode_tracks/<?php echo htmlspecialchars($track['fileBase']); ?>.mp3",
			"cover_art_url": "album_art/<?php echo htmlspecialchars($album['imageName']); ?>",

		},
<?php
}
?>
	],
});

	/*
		Ensure that on mouseover, CSS styles don't get messed up for active songs.
	*/
	jQuery('.song').on('mouseover', function(){
		jQuery(this).css('background-color', '#00A0FF');
		jQuery(this).find('.song-meta-data *').css('color', '#FFFFFF');

		if( !jQuery(this).hasClass('amplitude-active-song-container') ){
			jQuery(this).find('.play-button-container').css('display', 'block');
		}

		jQuery(this).find('.song-duration').css('color', '#FFFFFF');
	});

	/*
		Ensure that on mouseout, CSS styles don't get messed up for active songs.
	*/
	jQuery('.song').on('mouseout', function(){
		jQuery(this).css('background-color', '');
		jQuery(this).find('.song-meta-data *').css('color', '');
		jQuery(this).find('.play-button-container').css('display', 'none');
		jQuery(this).find('.song-duration').css('color', '');
	});

	/*
		Show and hide the play button container on the song when the song is clicked.
	*/
	jQuery('.song').on('click', function(){
		jQuery(this).find('.play-button-container').css('display', 'none');
	});


</script>
</body>
</html>
<?php
$db->close();
?>