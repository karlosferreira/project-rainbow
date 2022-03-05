<?php
defined('PHPFOX') or exit('NO DICE!');
/**
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 */
?>
<form method="post" action="#final" id="js_form" class="form">
    <h1>Administrators Account</h1>
    <div id="errors" class="hide"></div>
    <div class="form-group">
        <label for="email">Email</label>
        <input autofocus required class="form-control" type="email" name="val[email]" id="email" value="{value type='input' id='email'}" size="30" />
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" required type="password" name="val[password]" id="password" value="{value type='input' id='password'}" size="30" autocomplete="off" />
    </div>
    <input type="submit" value="Continue" class="hide" />
    <div class="help-block">
        If you encounter any problem, please follow our instruction in <a href="https://docs.phpfox.com/display/FOX4MAN/Installing+phpFox" target="_blank">this help topic</a> then try again.
    </div>
</form>