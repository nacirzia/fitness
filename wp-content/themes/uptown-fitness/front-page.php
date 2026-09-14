<?php
get_header();
$uri = get_template_directory_uri();
?>

<main id="main">
	<section class="home-hero">
		<img class="home-hero__photo" src="<?php echo esc_url( $uri . '/assets/images/hero.jpg' ); ?>" alt="Training im Uptown Fitness Club">
		<div class="hero-grain"></div>
		<div class="home-hero__content">
			<p class="eyebrow reveal">24/7 SMART GYM · DEIN TRAINING</p>
			<h1 class="reveal">DEIN GYM.<br><span>DEIN RHYTHMUS.</span></h1>
			<p class="hero-intro reveal">Mehr Freiheit, starkes Equipment und ein Club, der sich deinem Leben anpasst. Trainiere smart, flexibel und ohne Show.</p>
			<div class="hero-actions reveal">
				<a class="button" href="<?php echo esc_url( home_url( '/probetraining/' ) ); ?>">Gratis testen <span>↗</span></a>
				<a class="text-link" href="<?php echo esc_url( home_url( '/mitgliedschaft/' ) ); ?>">Mitgliedschaft entdecken <span>→</span></a>
			</div>
			<div class="hero-proof reveal">
				<div><strong>24/7</strong><span>Zugang</span></div>
				<div><strong>40+</strong><span>Trainingszonen</span></div>
				<div><strong>4.9</strong><span>Member Rating</span></div>
			</div>
		</div>
		<aside class="hero-card reveal" aria-label="Mitgliedskarte">
			<div class="hero-card__chip"></div>
			<p>UPTOWN CARD</p>
			<strong>24/7 ACCESS</strong>
			<span>Lea Sommer · Berlin Mitte</span>
			<small>Smart Gym · Digital Check-in</small>
		</aside>
		<div class="hero-stamp" aria-hidden="true"><span>MOVE YOUR WAY · UPTOWN · </span><b>U</b></div>
		<a href="#intro" class="scroll-cue" aria-label="Zum nächsten Abschnitt">SCROLL <i>↓</i></a>
	</section>

	<section class="intro-band" id="intro">
		<div class="section-shell intro-grid">
			<div class="section-index reveal">01 / UPTOWN</div>
			<div>
				<p class="eyebrow reveal">FITNESS, DAS ZU DIR PASST</p>
				<h2 class="display-title reveal">DU BESTIMMST,<br><span>WAS STARK HEISST.</span></h2>
			</div>
			<div class="intro-copy reveal">
				<p>Keine starren Zeiten. Keine unnötigen Extras. Bei Uptown Fitness bekommst du genau das, was dich weiterbringt: durchdachte Trainingsflächen, digitale Freiheit und echte Unterstützung.</p>
				<a class="text-link text-link--dark" href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>">Unsere Idee kennenlernen <span>↗</span></a>
			</div>
		</div>
	</section>

	<section class="split-showcase">
		<div class="split-showcase__copy reveal">
			<p class="eyebrow">DEIN TRAINING. DEINE REGELN.</p>
			<h2>ALLES, WAS DU FÜR DEIN TRAINING BRAUCHST.</h2>
			<p>Ob Kraft, Ausdauer, Mobility oder Functional Training: Unsere Zonen sind klar strukturiert, hochwertig ausgestattet und jederzeit für dich bereit.</p>
			<a class="button button--light" href="<?php echo esc_url( home_url( '/training/' ) ); ?>">Training entdecken <span>↗</span></a>
		</div>
		<figure class="split-showcase__image">
			<img src="<?php echo esc_url( $uri . '/assets/images/weights.jpg' ); ?>" alt="Krafttraining mit Freihanteln">
			<span class="vertical-label">BUILT FOR PROGRESS</span>
		</figure>
	</section>

	<section class="discipline-section">
		<div class="section-shell">
			<div class="section-heading">
				<div>
					<p class="eyebrow reveal">FINDE DEINEN FLOW</p>
					<h2 class="display-title reveal">WAS TREIBT<br>DICH AN?</h2>
				</div>
				<p class="section-heading__copy reveal">Vier Wege, ein Ziel: Du fühlst dich stärker als gestern. Wähle deinen Schwerpunkt oder kombiniere alles genau so, wie es für dich funktioniert.</p>
			</div>
			<div class="discipline-grid">
				<a class="discipline-card" href="<?php echo esc_url( home_url( '/training/krafttraining/' ) ); ?>">
					<img src="<?php echo esc_url( $uri . '/assets/images/weights.jpg' ); ?>" alt="Krafttraining">
					<span>01</span><h3>KRAFT</h3><p>Freihanteln, Maschinen und Racks für kontrollierten Fortschritt.</p><i>↗</i>
				</a>
				<a class="discipline-card" href="<?php echo esc_url( home_url( '/training/functional-training/' ) ); ?>">
					<img src="<?php echo esc_url( $uri . '/assets/images/functional.jpg' ); ?>" alt="Functional Training">
					<span>02</span><h3>FUNCTIONAL</h3><p>Bewegung, Stabilität und Athletik für deinen Alltag.</p><i>↗</i>
				</a>
				<a class="discipline-card" href="<?php echo esc_url( home_url( '/training/cardio-ausdauer/' ) ); ?>">
					<img src="<?php echo esc_url( $uri . '/assets/images/cardio.jpg' ); ?>" alt="Cardio Training">
					<span>03</span><h3>CARDIO</h3><p>Ausdauertraining mit smarter Leistungskontrolle.</p><i>↗</i>
				</a>
				<a class="discipline-card" href="<?php echo esc_url( home_url( '/training/yoga-mobility/' ) ); ?>">
					<img src="<?php echo esc_url( $uri . '/assets/images/yoga.jpg' ); ?>" alt="Yoga und Mobility">
					<span>04</span><h3>MOBILITY</h3><p>Mehr Beweglichkeit, Balance und aktive Regeneration.</p><i>↗</i>
				</a>
			</div>
		</div>
	</section>

	<section class="method-section">
		<div class="method-number" aria-hidden="true">365</div>
		<div class="section-shell method-grid">
			<div>
				<p class="eyebrow reveal">MEHR ALS NUR GERÄTE</p>
				<h2 class="display-title reveal">SO BRINGEN WIR<br>DICH WEITER.</h2>
			</div>
			<div class="method-list">
				<div class="method-item reveal"><span>01</span><div><h3>ANKOMMEN</h3><p>Starte mit einem klaren Check-in und lerne den Club in Ruhe kennen.</p></div></div>
				<div class="method-item reveal"><span>02</span><div><h3>PLANEN</h3><p>Definiere dein Ziel und erhalte einen Trainingsplan, der realistisch bleibt.</p></div></div>
				<div class="method-item reveal"><span>03</span><div><h3>TRAINIEREN</h3><p>Nutze moderne Zonen, Kurse und Coaching genau dann, wenn es passt.</p></div></div>
				<div class="method-item reveal"><span>04</span><div><h3>WACHSEN</h3><p>Tracke Fortschritte, passe deinen Plan an und bleib langfristig dran.</p></div></div>
			</div>
		</div>
	</section>

	<section class="app-section">
		<div class="section-shell app-grid">
			<div class="app-copy">
				<p class="eyebrow reveal">ALLES IN DEINER HAND</p>
				<h2 class="display-title reveal">DEIN CLUB.<br><span>DEINE APP.</span></h2>
				<p class="reveal">Digital einchecken, Auslastung sehen, Trainingsplan öffnen und Kurse buchen. Die Uptown App macht dein Training leichter, ohne es komplizierter zu machen.</p>
				<ul class="check-list reveal">
					<li>Mobiler 24/7-Zugang</li>
					<li>Live-Auslastung deines Clubs</li>
					<li>Trainingspläne & Fortschritt</li>
				</ul>
				<a class="button" href="<?php echo esc_url( home_url( '/uptown-app/' ) ); ?>">App kennenlernen <span>↗</span></a>
			</div>
			<div class="app-visual">
				<img class="app-visual__photo" src="<?php echo esc_url( $uri . '/assets/images/group.jpg' ); ?>" alt="Training mit der Uptown App">
				<div class="phone phone--back"><div class="phone-screen"><span>UPTOWN</span><b>Heute</b><small>45 Min · Upper Body</small></div></div>
				<div class="phone phone--front"><div class="phone-screen"><span>CHECK-IN</span><b>Bereit.</b><small>Club Berlin Mitte<br>Auslastung: entspannt</small><i>U</i></div></div>
			</div>
		</div>
	</section>

	<section class="numbers-section">
		<div class="section-shell">
			<div class="section-heading section-heading--white">
				<div><p class="eyebrow reveal">UPTOWN IN ZAHLEN</p><h2 class="display-title reveal">COMMUNITY,<br>DIE BEWEGT.</h2></div>
				<p class="reveal">Was klein begann, wächst mit jedem Menschen, der sich für sich selbst entscheidet.</p>
			</div>
			<div class="numbers-grid">
				<div class="number-card reveal"><strong data-counter="12800">0</strong><span>aktive Mitglieder</span></div>
				<div class="number-card reveal"><strong data-counter="18">0</strong><span>moderne Clubs</span></div>
				<div class="number-card reveal"><strong data-counter="96">0</strong><span>Prozent Empfehlung</span></div>
				<div class="number-card reveal"><strong data-counter="24">0</strong><span>Stunden Zugang</span></div>
			</div>
		</div>
	</section>

	<section class="map-section">
		<div class="section-shell map-grid">
			<div>
				<p class="eyebrow reveal">SMART. NAH. FÜR DICH.</p>
				<h2 class="display-title reveal">FINDE DEINEN<br>UPTOWN CLUB.</h2>
				<p class="reveal">18 moderne Clubs in ganz Deutschland. 24/7 offen, digital erreichbar und immer in deiner Nähe.</p>
				<div class="map-stats">
					<div class="reveal"><strong data-counter="18">0</strong><span>Clubs</span></div>
					<div class="reveal"><strong data-counter="12800">0</strong><span>Mitglieder</span></div>
					<div class="reveal"><strong data-counter="160">0</strong><span>Kurse / Woche</span></div>
				</div>
				<a class="button" href="<?php echo esc_url( home_url( '/standorte/' ) ); ?>">Standorte ansehen <span>↗</span></a>
			</div>
			<figure class="map-photo">
				<img src="<?php echo esc_url( $uri . '/assets/images/facility.jpg' ); ?>" alt="Uptown Fitness Club">
				<figcaption class="map-photo__badge">18 Clubs · Deutschland</figcaption>
			</figure>
		</div>
	</section>

	<section class="club-feature">
		<img class="club-feature__photo" src="<?php echo esc_url( $uri . '/assets/images/studio.jpg' ); ?>" alt="Modernes Equipment im Uptown Club">
		<div class="club-feature__overlay"></div>
		<div class="club-feature__content reveal">
			<p class="eyebrow">SMART. NAH. FÜR DICH.</p>
			<h2>EQUIPMENT,<br>DAS MITZIEHT.</h2>
			<p>Freihanteln, Maschinen, Functional Areas und Recovery – alles an einem Ort, jederzeit bereit.</p>
			<a class="button" href="<?php echo esc_url( home_url( '/studios-ausstattung/' ) ); ?>">Studios entdecken <span>↗</span></a>
		</div>
	</section>

	<section class="membership-teaser">
		<div class="section-shell membership-grid">
			<figure class="membership-image">
				<img src="<?php echo esc_url( $uri . '/assets/images/membership.jpg' ); ?>" alt="Mitgliedschaft bei Uptown Fitness">
			</figure>
			<div class="membership-copy">
				<p class="eyebrow reveal">EINFACH STARTEN</p>
				<h2 class="display-title reveal">DEINE MITGLIEDSCHAFT.<br>KLAR & FAIR.</h2>
				<p class="reveal">Wähle die Laufzeit, die zu dir passt. Immer inklusive: 24/7 Zugang, App, Trainingsplan und Support. Keine versteckten Extras.</p>
				<div class="price-line reveal"><span>ab</span><strong>29,90 €</strong><small>/ Monat</small></div>
				<a class="button" href="<?php echo esc_url( home_url( '/preise/' ) ); ?>">Tarife vergleichen <span>↗</span></a>
			</div>
		</div>
	</section>

	<section class="journal-section">
		<div class="section-shell">
			<div class="section-heading">
				<div><p class="eyebrow reveal">UPTOWN MAGAZIN</p><h2 class="display-title reveal">WISSEN, DAS<br>DICH WEITERBRINGT.</h2></div>
				<a class="text-link text-link--dark reveal" href="<?php echo esc_url( home_url( '/magazin/' ) ); ?>">Alle Beiträge <span>↗</span></a>
			</div>
			<div class="journal-grid">
				<?php
				$posts = get_posts( array( 'posts_per_page' => 3, 'post_status' => 'publish' ) );
				foreach ( $posts as $index => $post ) :
					setup_postdata( $post );
					?>
					<a class="journal-card reveal" href="<?php the_permalink(); ?>">
						<figure class="journal-card__image">
							<img src="<?php echo esc_url( uptown_image( get_the_ID(), array( 'nutrition', 'trainer', 'recovery' )[ $index ] ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
						</figure>
						<span>MAGAZIN · <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></span>
						<h3><?php the_title(); ?></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<i>Artikel lesen ↗</i>
					</a>
				<?php endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>

	<section class="coach-strip">
		<figure class="coach-strip__media">
			<img src="<?php echo esc_url( $uri . '/assets/images/trainer.jpg' ); ?>" alt="Uptown Fitness Coach">
		</figure>
		<div class="coach-strip__copy reveal">
			<p class="eyebrow">COACHES, DIE DICH SEHEN</p>
			<h2>STARKE BEGLEITUNG.<br>OHNE DRUCK.</h2>
			<p>Unsere Trainerinnen und Trainer erklären Technik, passen Pläne an und lassen dir den Raum, deinen eigenen Rhythmus zu finden.</p>
			<a class="button button--light" href="<?php echo esc_url( home_url( '/coaches/' ) ); ?>">Team kennenlernen <span>↗</span></a>
		</div>
	</section>

	<section class="final-cta">
		<div class="section-shell reveal">
			<p class="eyebrow">BEREIT, WENN DU ES BIST</p>
			<h2>NICHT IRGENDWANN.<br><span>JETZT.</span></h2>
			<p>Teste Uptown Fitness kostenlos und finde heraus, wie gut sich Training anfühlen kann, wenn alles passt.</p>
			<a class="button button--light" href="<?php echo esc_url( home_url( '/probetraining/' ) ); ?>">Probetraining buchen <span>↗</span></a>
		</div>
	</section>
</main>

<?php get_footer(); ?>
