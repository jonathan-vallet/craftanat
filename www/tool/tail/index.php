<?php
// Starts session before header is sent
session_start();

require_once('../../../init/initLog.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<style type="text/css">
		body
		{
			background: black;
			color: white;
			margin-top: 35px;
		}
		
		p
		{
			font-size: 0.9em;
			margin: 0.2em;
		}
		
		p#toolbar
		{
			position: fixed;
			line-height: 30px;
			border-bottom: 1px solid #BBB;
			width: 100%;
			background: black;
			top: 0;
			margin: 0;
		}
		
		#clear
		{
			cursor: pointer;
		}
	</style>
	<script type="text/javascript" src="<?php echo JS_DIR; ?>ext/jquery-1.7.1.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			setInterval(tailLogs, 1000);
			tailLogs();
			function tailLogs()
			{
				$.ajax({
					url: 'ajax.php',
					type: 'get',
					data: {
						grep: $('#grep').val()
					},
					success: function(responseText)
					{
						$('#logs').append(responseText);
						$('#logs').attr({ scrollTop: $('#logs').attr('scrollHeight') });
						if($('#scroll').attr('checked'))
							window.scrollTo(0,document.body.scrollHeight);
					}
				});
			}
			
			$('#clear').click(function()
			{
				$('#logs').html('');
			});
		});
	</script>
</head>
<body>
	<p id="toolbar">
		<label for="grep">Filtrer:
		<input type="text" id="grep" />
		 - <span id="clear">Clear</span>
		 - <label for="scroll">Scroll</label><input type="checkbox" id="scroll" checked="checked" />
		
	<div id="logs">
	</div>
</body>
</html>
