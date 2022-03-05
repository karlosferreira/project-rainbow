<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php 
 
 

 if ($this->_aVars['bHtml']): ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body id="page_<?php echo Phpfox::getLib('module')->getPageId(); ?>">
<?php if ($this->_aVars['bMessageHeader']): ?>
<?php if (isset ( $this->_aVars['sMessageHello'] )): ?>
<?php echo $this->_aVars['sMessageHello']; ?>
<?php else: ?>
<?php echo _p('hello_comma'); ?>
<?php endif; ?>
    <br />
    <br />
<?php endif; ?>
<?php echo $this->_aVars['sMessage']; ?>
    <br />
    <br />
<?php echo $this->_aVars['sEmailSig']; ?>
</body>
</html>
<?php else: ?>
<?php if ($this->_aVars['bMessageHeader']): ?>
<?php if (isset ( $this->_aVars['sMessageHello'] )): ?>
<?php echo $this->_aVars['sMessageHello']; ?>
<?php else: ?>
<?php echo _p('hello_comma'); ?>
<?php endif;  endif; ?>
<?php echo $this->_aVars['sMessage']; ?>

<?php echo $this->_aVars['sEmailSig']; ?>
<?php endif; ?>
