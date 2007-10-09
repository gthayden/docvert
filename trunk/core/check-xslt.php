<html>
<body>
<h1>PHP4 function xslt_create() ?</h1>
<p>
	<?php
	if(function_exists('xslt_create'))
		{
		print 'Yes it exists';
		}
	else
		{
		print 'No it does not exist';
		}
	?>
</p>
<h1>PHP5 class XSLTProcessor ?</h1>
<p>
	<?php
	if(class_exists('XSLTProcessor'))
		{
		print 'Yes it exists';
		}
	else
		{
		print 'No it does not exist';
		}
	?>
</p>

</body>
</html>
