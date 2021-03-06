<div class="ifm-container">
    <div class="ifm-row">
        <div class="ifm-col-12">
            <div id="password-lost-form" class="widecolumn">
                <?php if ($params['show_title']) : ?>
                    <h3><?php _e('Forgot Your Password?', 'personalize-login'); ?></h3>
                <?php endif; ?>
                <?php if (count($params['errors']) > 0) : ?>
                    <?php foreach ($params['errors'] as $error) : ?>
                        <p class="ifm-error">
                            <?php echo $error; ?>
                        </p>
                    <?php endforeach; ?>
                <?php endif; ?>
                <p>
                    <?php
                    _e(
                        "Enter your username and we'll send a reset password link to the associated account.",
                        'personalize_login'
                    );
                    ?>
                </p>
                <form id="lostpasswordform" action="<?php echo wp_lostpassword_url(); ?>" method="post">
                    <p class="form-row">
                        <label for="user_login"><?php _e('Username', 'personalize-login'); ?>
                            <input type="text" name="user_login" id="user_login">
                    </p>

                    <p class="lostpassword-submit">
                        <input type="submit" name="submit" class="lostpassword-button" value="<?php _e('Reset Password', 'personalize-login'); ?>" />
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>