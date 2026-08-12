<?php

/**
 * A redirection form's view a user could hand to the payment manager with
 * `Payment::setRedirectionFormViewPath()`. It is rendered with blade, so the
 * form below receives a CSRF field on the way.
 */

?>
<form method="<?php echo $method; ?>" action="<?php echo $action; ?>">
    custom-view:<?php echo $method; ?>:<?php echo $action; ?>:<?php echo implode(',', array_keys($inputs)); ?>
</form>
