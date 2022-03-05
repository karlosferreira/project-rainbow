<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:03 pm */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $this->_aVars['sLocaleDirection']; ?>" lang="<?php echo $this->_aVars['sLocaleCode']; ?>">
	<head>
		<title><?php echo $this->getTitle(); ?></title>	
<?php echo $this->getHeader(); ?>
	</head>
	<body id="page_<?php echo Phpfox::getLib('module')->getPageId(); ?>">
		<div id="admincp_base"></div>
<?php if (!$this->bIsSample): ?><div id="site_content"><?php if (isset($this->_aVars['bSearchFailed'])): ?><div class="message">Unable to find anything with your search criteria.</div><?php else:  $sController = "admincp.login";  Phpfox::getLib('phpfox.module')->getControllerTemplate();  endif; ?></div><?php endif; ?>
<?php echo $this->_sFooter; ?>
	</body>
</html>
