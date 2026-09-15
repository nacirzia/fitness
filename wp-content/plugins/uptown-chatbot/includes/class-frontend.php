<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Chatbot_Frontend {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'markup' ), 5 );
	}

	public static function is_active() {
		if ( is_admin() ) {
			return false;
		}
		$settings = Uptown_Chatbot_Settings::get();
		return '1' === $settings['enabled'];
	}

	public static function assets() {
		if ( ! self::is_active() ) {
			return;
		}

		$settings = Uptown_Chatbot_Settings::get();

		wp_enqueue_style(
			'uptown-chatbot-widget',
			UPTOWN_CHATBOT_URL . 'assets/css/widget.css',
			array(),
			filemtime( UPTOWN_CHATBOT_DIR . 'assets/css/widget.css' )
		);
		wp_enqueue_script(
			'uptown-chatbot-widget',
			UPTOWN_CHATBOT_URL . 'assets/js/widget.js',
			array(),
			filemtime( UPTOWN_CHATBOT_DIR . 'assets/js/widget.js' ),
			true
		);
		wp_localize_script(
			'uptown-chatbot-widget',
			'UptownChatbot',
			array(
				'restUrl'    => esc_url_raw( rest_url( 'uptown-chatbot/v1/chat' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'theme'      => $settings['theme_color'],
				'botName'    => $settings['bot_name'],
				'language'   => $settings['language'],
				'configured' => ! empty( $settings['api_key'] ),
				'i18n'       => self::strings(),
			)
		);
	}

	public static function markup() {
		if ( ! self::is_active() ) {
			return;
		}

		$settings = Uptown_Chatbot_Settings::get();
		$name     = $settings['bot_name'] ? $settings['bot_name'] : 'Uptown';
		?>
		<div id="uptown-chatbot" class="ucb" style="--ucb-theme:<?php echo esc_attr( $settings['theme_color'] ); ?>" hidden>
			<div class="ucb-panel" role="dialog" aria-modal="false" aria-labelledby="ucb-title" hidden>
				<header class="ucb-header">
					<button type="button" class="ucb-icon-btn" data-ucb="minimize" aria-label="Minimize">
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<strong id="ucb-title" class="ucb-title"><?php echo esc_html( $name ); ?></strong>
					<button type="button" class="ucb-icon-btn" data-ucb="menu" aria-expanded="false" aria-controls="ucb-menu" aria-label="Menu">
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>
					</button>
					<div id="ucb-menu" class="ucb-menu" hidden>
						<button type="button" data-ucb="lang" data-lang="de">Deutsch</button>
						<button type="button" data-ucb="lang" data-lang="en">English</button>
						<button type="button" data-ucb="reset">New chat</button>
					</div>
				</header>
				<div class="ucb-body">
					<div class="ucb-divider" data-ucb="divider"></div>
					<div class="ucb-messages" data-ucb="messages"></div>
					<div class="ucb-chips" data-ucb="chips"></div>
				</div>
				<form class="ucb-composer" data-ucb="form">
					<label class="screen-reader-text" for="ucb-input">Message</label>
					<input id="ucb-input" type="text" data-ucb="input" maxlength="1000" autocomplete="off">
					<button type="submit" class="ucb-send" data-ucb="send" aria-label="Send">
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M2 21 23 12 2 3v7l15 2-15 2v7z"/></svg>
					</button>
				</form>
			</div>
			<div class="ucb-dock">
				<div class="ucb-tip" data-ucb="tip">
					<span data-ucb="tip-text"></span>
					<button type="button" class="ucb-tip-ok" data-ucb="tip-ok">Got it</button>
				</div>
				<p class="ucb-powered" data-ucb="powered"></p>
				<button type="button" class="ucb-launcher" data-ucb="toggle" aria-label="Open chat">
					<span class="ucb-launcher-open" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="26" height="26"><path fill="currentColor" d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
					</span>
					<span class="ucb-launcher-close" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="26" height="26"><path fill="currentColor" d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
					</span>
				</button>
			</div>
		</div>
		<?php
	}

	private static function strings() {
		return array(
			'de' => array(
				'welcome'    => 'Hi! Ich bin {name}, dein Uptown Fitness Assistent. Frag mich zu Öffnungszeiten, Preisen, Standorten oder Probetraining.',
				'newMessages'=> 'Neue Nachrichten',
				'placeholder'=> 'Nachricht eingeben und Enter drücken...',
				'powered'    => 'Powered by Uptown Fitness',
				'tip'        => 'Fragen zu Zeiten & Preisen?',
				'tipOk'      => 'Verstanden',
				'sendError'  => 'Leider keine Antwort. Bitte versuche es gleich noch einmal.',
				'offline'    => 'Der Chatbot ist noch nicht konfiguriert. Bitte hinterlege den Gemini-Schlüssel in den Einstellungen.',
				'open'       => 'Chat öffnen',
				'close'      => 'Chat schließen',
				'reset'      => 'Neuer Chat',
				'chips'      => array(
					array( 'label' => 'Preise', 'message' => 'Was kostet die Mitgliedschaft?' ),
					array( 'label' => 'Öffnungszeiten', 'message' => 'Wann hat der Club geöffnet?' ),
					array( 'label' => 'Probetraining', 'message' => 'Wie buche ich ein Probetraining?' ),
					array( 'label' => 'Andere Frage', 'message' => 'Ich habe eine andere Frage' ),
				),
			),
			'en' => array(
				'welcome'    => "Hi! I'm {name}, your Uptown Fitness assistant. Ask me about opening hours, fees, locations, or a free trial.",
				'newMessages'=> 'New messages',
				'placeholder'=> 'Type and press enter...',
				'powered'    => 'Powered by Uptown Fitness',
				'tip'        => 'Questions about hours & prices?',
				'tipOk'      => 'Got it',
				'sendError'  => 'No reply this time. Please try again in a moment.',
				'offline'    => 'The chatbot is not configured yet. Add a Gemini API key in the settings.',
				'open'       => 'Open chat',
				'close'      => 'Close chat',
				'reset'      => 'New chat',
				'chips'      => array(
					array( 'label' => 'Membership cost', 'message' => 'What does membership cost?' ),
					array( 'label' => 'Gym hours', 'message' => 'When is the gym open?' ),
					array( 'label' => 'Book a trial', 'message' => 'How do I book a trial?' ),
					array( 'label' => 'Other question', 'message' => 'I have a different question' ),
				),
			),
		);
	}
}
