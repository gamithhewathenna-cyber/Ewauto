<?php
// Shared enquiry-form handling used by both the full Contact Us page and
// the "Talk to Our Team" popup that appears on every page, so the two
// forms behave identically and validation/mail logic lives in one place.

function handle_contact_submission(array $content): array
{
    if (!empty($_POST['website'])) {
        // Honeypot field caught a bot — pretend success, do nothing.
        return ['ok' => true, 'message' => content($content, 'form_success_message', "Thanks for reaching out! We'll get back to you soon.")];
    }

    if (!check_csrf()) {
        return ['ok' => false, 'message' => 'Your session expired. Please refresh the page and try again.'];
    }

    $first   = trim($_POST['first_name'] ?? '');
    $last    = trim($_POST['last_name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($first === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Please fill in your name, a valid email address, and a message.'];
    }

    $to      = content($content, 'contact_email', 'companyname@gamil.com');
    $subject = 'New website enquiry from ' . $first . ' ' . $last;
    $body    = "Name: $first $last\nPhone: $phone\nEmail: $email\n\nMessage:\n$message";
    $headers = 'Reply-To: ' . $email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
    @mail($to, $subject, $body, $headers);

    return ['ok' => true, 'message' => content($content, 'form_success_message', "Thanks for reaching out! We'll get back to you soon.")];
}

function render_talk_to_team_widget(array $content, string $token): void
{
    $c = static fn(string $key, string $fallback = '') => content($content, $key, $fallback);
    ?>
    <button type="button" class="talk-team-btn" id="talkTeamBtn" aria-haspopup="dialog" aria-controls="talkTeamModal">
        <span class="icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v10a2 2 0 01-2 2H9l-5 4V6a2 2 0 012-2z"/></svg></span>
        <span class="label">Talk to Our Team</span>
    </button>

    <div class="contact-modal" id="talkTeamModal" aria-hidden="true">
        <div class="contact-modal-backdrop" data-modal-close></div>
        <div class="contact-modal-panel" role="dialog" aria-modal="true" aria-label="Talk to our team">
            <button type="button" class="contact-modal-close" data-modal-close aria-label="Close">&times;</button>

            <h2 class="form-heading"><?= e($c('form_heading', 'Send us a message')) ?></h2>
            <p class="form-subtext"><?= e($c('form_subtext', 'Do you have a question? A complaint? Or need any help? Feel free to contact us.')) ?></p>

            <div class="alert ok form-alert" data-form-notice hidden></div>
            <div class="alert error form-alert" data-form-error hidden></div>

            <form method="post" class="contact-form" id="talkTeamForm" action="<?= e(BASE_URL) ?>/contact-submit.php">
                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="form-row">
                    <label class="form-field">First Name
                        <input type="text" name="first_name" placeholder="Enter your first name" required>
                    </label>
                    <label class="form-field">Last Name
                        <input type="text" name="last_name" placeholder="Enter your last name">
                    </label>
                </div>
                <div class="form-row">
                    <label class="form-field">Email
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </label>
                    <label class="form-field">Contact Number
                        <input type="text" name="phone" placeholder="Enter your contact number">
                    </label>
                </div>
                <label class="form-field form-field-full">Message
                    <textarea name="message" rows="5" placeholder="Enter your message" required></textarea>
                </label>

                <div class="contact-form-actions">
                    <button type="submit" class="btn-send"><?= e($c('form_button_label', 'Send a Message')) ?> <span class="arrow">&rsaquo;</span></button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
