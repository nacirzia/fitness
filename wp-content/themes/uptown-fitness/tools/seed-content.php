<?php
/**
 * Idempotent content importer for the Uptown Fitness demo site.
 *
 * Run: wp eval-file wp-content/themes/uptown-fitness/tools/seed-content.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run this file through WordPress.\n" );
}

function uptown_cards_html( $cards ) {
	if ( empty( $cards ) ) {
		return '';
	}
	$html = '<div class="feature-list">';
	foreach ( $cards as $card ) {
		$html .= '<div><h3>' . esc_html( $card[0] ) . '</h3><p>' . esc_html( $card[1] ) . '</p></div>';
	}
	return $html . '</div>';
}

function uptown_page_html( $data ) {
	$html = '<p>' . wp_kses_post( $data['intro'] ) . '</p>';
	if ( ! empty( $data['metrics'] ) ) {
		$html .= '<div class="metric-grid">';
		foreach ( $data['metrics'] as $metric ) {
			$html .= '<div><strong>' . esc_html( $metric[0] ) . '</strong><span>' . esc_html( $metric[1] ) . '</span></div>';
		}
		$html .= '</div>';
	}
	foreach ( $data['sections'] as $section ) {
		$html .= '<h2>' . esc_html( $section[0] ) . '</h2><p>' . wp_kses_post( $section[1] ) . '</p>';
		if ( ! empty( $section[2] ) ) {
			$html .= uptown_cards_html( $section[2] );
		}
		if ( ! empty( $section[3] ) ) {
			$html .= '<h3>' . esc_html( $section[3][0] ) . '</h3><p>' . wp_kses_post( $section[3][1] ) . '</p>';
		}
	}
	if ( ! empty( $data['form'] ) ) {
		$html .= '<div class="contact-form-wrap"><h2>SCHREIB UNS</h2>';
		if ( isset( $_GET['sent'] ) ) {
			$html .= '<p class="form-success">Danke! Deine Nachricht ist bei uns angekommen.</p>';
		}
		$html .= '<form class="contact-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">'
			. wp_nonce_field( 'uptown_contact', 'uptown_nonce', true, false )
			. '<input type="hidden" name="action" value="uptown_contact">'
			. '<label>Dein Name<input type="text" name="name" autocomplete="name" required></label>'
			. '<label>E-Mail<input type="email" name="email" autocomplete="email" required></label>'
			. '<label>Telefon (optional)<input type="tel" name="phone" autocomplete="tel"></label>'
			. '<label>Worum geht es?<select name="topic"><option>Probetraining</option><option>Mitgliedschaft</option><option>Firmenfitness</option><option>Support</option></select></label>'
			. '<label class="form-wide">Deine Nachricht<textarea name="message" rows="6" required></textarea></label>'
			. '<label class="form-hp" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>'
			. '<button class="button" type="submit">Nachricht senden <span>↗</span></button>'
			. '</form></div>';
	}
	if ( ! empty( $data['quote'] ) ) {
		$html .= '<blockquote><p>“' . esc_html( $data['quote'] ) . '”</p></blockquote>';
	}
	$html .= '<div class="callout"><h2>' . esc_html( $data['cta_title'] ?? 'BEREIT FÜR DEINEN START?' ) . '</h2><p>' .
		esc_html( $data['cta_text'] ?? 'Lerne Uptown Fitness unverbindlich kennen und finde heraus, wie dein Training aussehen kann.' ) .
		'</p><a class="button button--light" href="' . esc_url( home_url( '/probetraining/' ) ) . '">Kostenlos testen <span>↗</span></a></div>';
	return $html;
}

function uptown_upsert( $type, $data ) {
	$existing = get_page_by_path( $data['slug'], OBJECT, $type );
	$postarr  = array(
		'ID'           => $existing ? $existing->ID : 0,
		'post_type'    => $type,
		'post_status'  => 'publish',
		'post_title'   => $data['title'],
		'post_name'    => $data['slug'],
		'post_excerpt' => $data['excerpt'] ?? $data['lead'],
		'post_content' => $data['content'] ?? uptown_page_html( $data ),
		'menu_order'   => $data['order'] ?? 0,
	);
	$post_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( $data['title'] . ': ' . $post_id->get_error_message() );
		return 0;
	}
	update_post_meta( $post_id, '_uptown_image', $data['image'] ?? 'facility' );
	update_post_meta( $post_id, '_uptown_eyebrow', $data['eyebrow'] ?? 'UPTOWN FITNESS' );
	update_post_meta( $post_id, '_uptown_lead', $data['lead'] ?? '' );
	update_post_meta( $post_id, '_uptown_meta_description', $data['meta'] ?? $data['lead'] );
	return $post_id;
}

$pages = array(
	array(
		'title' => 'Startseite', 'slug' => 'startseite', 'image' => 'hero', 'eyebrow' => '24/7 SMART GYM',
		'lead' => 'Dein modernes 24/7 Gym für Kraft, Ausdauer, Mobility und echte Fortschritte.',
		'meta' => 'Uptown Fitness: Dein smartes 24/7 Gym mit modernem Equipment, starken Kursen und persönlichem Coaching. Jetzt kostenlos testen.',
		'intro' => 'Willkommen bei Uptown Fitness.', 'sections' => array(),
	),
	array(
		'title' => 'Über uns', 'slug' => 'ueber-uns', 'image' => 'community', 'eyebrow' => 'DAS IST UPTOWN',
		'lead' => 'Wir machen hochwertiges Training zugänglich, flexibel und so unkompliziert, dass es wirklich in dein Leben passt.',
		'meta' => 'Lerne Uptown Fitness kennen: moderne 24/7 Clubs, klare Angebote, engagierte Coaches und eine Community, die Fortschritt gemeinsam denkt.',
		'intro' => 'Uptown Fitness ist aus einer einfachen Beobachtung entstanden: Viele Menschen wollen sich bewegen, scheitern aber nicht an Motivation, sondern an komplizierten Verträgen, unübersichtlichen Studios und Trainingsangeboten ohne Orientierung. Wir wollten einen Ort schaffen, der diese Hürden konsequent abbaut.',
		'metrics' => array( array( '18', 'Clubs' ), array( '12.800+', 'Mitglieder' ), array( '96%', 'Empfehlung' ), array( '24/7', 'Zugang' ) ),
		'sections' => array(
			array( 'FITNESS OHNE FASSADE', 'Bei uns geht es nicht darum, wie Training aussieht, sondern was es für dich bewirkt. Moderne Trainingsflächen, verständliche Prozesse und aufmerksame Menschen sorgen dafür, dass du dich vom ersten Besuch an zurechtfindest.', array(
				array( 'Klarheit', 'Transparente Tarife, eindeutige Leistungen und keine versteckten Überraschungen.' ),
				array( 'Qualität', 'Ausgewähltes Equipment, gepflegte Clubs und fortlaufend geschulte Coaches.' ),
				array( 'Freiheit', '24/7 trainieren, digital einchecken und Angebote flexibel kombinieren.' ),
			) ),
			array( 'WIR WACHSEN MIT DIR', 'Unsere Clubs entwickeln sich gemeinsam mit ihrer Community. Feedback fließt direkt in neue Zonen, Kursformate und digitale Funktionen. So entsteht kein Fitnesskonzept am Reißbrett, sondern ein lebendiger Ort, an dem Menschen langfristig gern trainieren.', array(), array( 'Unser Versprechen', 'Du bekommst ehrliche Unterstützung ohne Druck. Wir zeigen dir Optionen, erklären Zusammenhänge und lassen dir die Freiheit, deinen eigenen Rhythmus zu finden.' ) ),
		),
		'quote' => 'Stärke ist persönlich. Deshalb sollte dein Training nicht in eine Schablone passen.',
		'cta_title' => 'LERNE UNS KENNEN',
	),
	array(
		'title' => 'Mission & Werte', 'slug' => 'mission-werte', 'image' => 'trainer', 'eyebrow' => 'WOFÜR WIR STEHEN',
		'lead' => 'Fortschritt entsteht, wenn Qualität, Orientierung und Freiheit zusammenkommen.',
		'meta' => 'Entdecke Mission und Werte von Uptown Fitness: zugängliches Training, ehrliche Beratung, moderne Standards und langfristige Gesundheit.',
		'intro' => 'Unsere Mission ist es, Menschen dabei zu unterstützen, Bewegung zu einem selbstverständlichen Teil ihres Lebens zu machen. Nicht mit kurzfristigen Versprechen, sondern mit Bedingungen, die langfristigen Fortschritt ermöglichen.',
		'sections' => array(
			array( 'UNSERE MISSION', 'Wir verbinden hochwertige Fitnessangebote mit digitaler Einfachheit und menschlicher Nähe. Jede Entscheidung wird daran gemessen, ob sie dein Training klarer, sicherer oder wirksamer macht.', array(
				array( 'Zugänglich', 'Training für unterschiedliche Körper, Erfahrungen, Ziele und Tagesabläufe.' ),
				array( 'Verlässlich', 'Gepflegte Räume, funktionierendes Equipment und nachvollziehbare Standards.' ),
				array( 'Wirksam', 'Durchdachte Trainingsreize statt kurzfristiger Trends und leerer Versprechen.' ),
			) ),
			array( 'WERTE, DIE MAN SPÜRT', 'Respekt zeigt sich bei uns in kleinen Dingen: ein freundlicher Empfang, Platz für dein Tempo und Hilfe, wenn du sie brauchst. Verantwortung bedeutet, realistische Ziele zu setzen und Training so zu planen, dass es zu deinem Alltag und deiner Gesundheit passt.', array(), array( 'Nachhaltiger Fortschritt', 'Wir feiern nicht nur schnelle Ergebnisse. Wir helfen dir, Routinen zu entwickeln, die nach zwölf Wochen ebenso funktionieren wie nach zwei Jahren.' ) ),
		),
		'quote' => 'Nicht perfekter werden. Sondern stärker, sicherer und konsequenter.',
	),
	array(
		'title' => 'Warum Uptown', 'slug' => 'warum-uptown', 'image' => 'woman', 'eyebrow' => 'DER UPTOWN UNTERSCHIED',
		'lead' => 'Ein Club, der dir Freiheit gibt und trotzdem Orientierung bietet.',
		'meta' => 'Warum Uptown Fitness? 24/7 Zugang, moderne Trainingszonen, digitale App, faire Tarife und persönliche Unterstützung in einer starken Community.',
		'intro' => 'Ein gutes Gym bietet Geräte. Ein sehr gutes Gym hilft dir, sie sinnvoll zu nutzen, bleibt in deinem Alltag erreichbar und motiviert dich, wiederzukommen. Genau darauf ist Uptown Fitness ausgelegt.',
		'sections' => array(
			array( 'SECHS GUTE GRÜNDE', 'Vom ersten Check-in bis zum langfristigen Trainingsplan greifen alle Bausteine ineinander. Du entscheidest, wie viel Begleitung du möchtest.', array(
				array( '24/7 offen', 'Trainiere morgens, spätabends oder dazwischen – dein Rhythmus zählt.' ),
				array( 'Smart geplant', 'Klare Trainingszonen und die App reduzieren Reibung und Wartezeit.' ),
				array( 'Fair kalkuliert', 'Verständliche Tarife mit Leistungen, die du wirklich nutzt.' ),
				array( 'Gut begleitet', 'Coaches erklären Bewegungen und helfen bei der sinnvollen Planung.' ),
				array( 'Sauber & sicher', 'Tägliche Checks, gepflegte Flächen und nachvollziehbare Clubregeln.' ),
				array( 'Gemeinsam stärker', 'Eine offene Community ohne Show, Einschüchterung oder Leistungsdruck.' ),
			) ),
			array( 'VON ANFANG AN ORIENTIERT', 'Bei deinem Start zeigen wir dir den Club, besprechen dein Ziel und geben dir einen einfachen Plan für die ersten Wochen. Damit du nicht nur Mitglied wirst, sondern ins Training kommst.', array(), array( 'Einfach ausprobieren', 'Buche ein kostenloses Probetraining. Du erhältst eine Tour, kannst die Trainingszonen testen und stellst alle Fragen direkt vor Ort.' ) ),
		),
		'quote' => 'Ich hatte zum ersten Mal das Gefühl, dass ein Studio meinen Alltag versteht.',
	),
	array(
		'title' => 'Studios & Ausstattung', 'slug' => 'studios-ausstattung', 'image' => 'facility', 'eyebrow' => 'RAUM FÜR FORTSCHRITT',
		'lead' => 'Durchdachte Zonen, hochwertiges Equipment und genug Platz für konzentriertes Training.',
		'meta' => 'Entdecke die Uptown Fitness Studios: moderne Kraft-, Cardio-, Functional- und Mobility-Zonen, gepflegte Umkleiden und smarte Ausstattung.',
		'intro' => 'Unsere Clubs sind so aufgebaut, dass du schnell findest, was du brauchst. Jede Zone hat eine klare Funktion, kurze Wege und genug Raum für sichere Bewegungen – vom ersten Warm-up bis zum letzten Mobility-Drill.',
		'metrics' => array( array( '40+', 'Trainingszonen' ), array( '150+', 'Geräte je Club' ), array( '4×', 'Tägliche Checks' ), array( '100%', 'Fokus' ) ),
		'sections' => array(
			array( 'ALLES AN SEINEM PLATZ', 'Freihantelbereiche, plate-loaded Maschinen, Kabelzüge und Racks bilden das Zentrum unserer Kraftflächen. Cardioequipment mit klaren Leistungsdaten ergänzt das Training. Functional Areas bieten Platz für freie Bewegung, Schlitten, Kettlebells und Körpergewicht.', array(
				array( 'Strength Zone', 'Racks, Bänke, Kurzhanteln, Langhanteln und Maschinen für alle Leistungsstufen.' ),
				array( 'Cardio Deck', 'Laufbänder, Bikes, Rower und Stepper mit intuitiver Bedienung.' ),
				array( 'Mobility Area', 'Matten, Rollen, Bänder und ruhige Flächen für Warm-up und Recovery.' ),
			) ),
			array( 'SAUBERKEIT IST STANDARD', 'Unsere Teams kontrollieren Geräte und Flächen mehrmals täglich. Wartungen werden dokumentiert und Defekte priorisiert behoben. Gute Beleuchtung, klare Sichtachsen und ausgewählte Bodenbeläge schaffen eine Atmosphäre, in der du konzentriert und sicher trainierst.', array(), array( 'Barrierearm gedacht', 'Breite Wege, verständliche Beschilderung und ansprechbare Teams erleichtern die Nutzung für möglichst viele Menschen.' ) ),
		),
	),
	array(
		'title' => 'Mitgliedschaft', 'slug' => 'mitgliedschaft', 'image' => 'membership', 'eyebrow' => 'EINFACH MITGLIED WERDEN',
		'lead' => 'Ein Tarif, der zu deinem Alltag passt. Klare Leistungen, digitale Verwaltung und volle Trainingsfreiheit.',
		'meta' => 'Uptown Fitness Mitgliedschaft: flexible Laufzeiten, 24/7 Clubzugang, App, Trainingsplan und faire Konditionen. Jetzt Tarif auswählen.',
		'intro' => 'Mit deiner Uptown Mitgliedschaft erhältst du Zugang zu allen Kernbereichen des Clubs. Du verwaltest deine Daten digital, siehst die aktuelle Auslastung und kannst Zusatzangebote nur dann buchen, wenn du sie wirklich brauchst.',
		'sections' => array(
			array( 'IMMER DABEI', 'Jeder Tarif enthält die wichtigsten Bausteine für selbstständiges Training. Unterschiede gibt es vor allem bei Laufzeit, Clubflexibilität und Coachingumfang.', array(
				array( '24/7 Clubzugang', 'Sicherer Check-in über deine persönliche Mitgliedschaft und die Uptown App.' ),
				array( 'Startplan', 'Ein verständlicher Trainingsplan für deine ersten Wochen im Club.' ),
				array( 'Digitale Verwaltung', 'Vertragsdaten, Besuche, Auslastung und Support an einem Ort.' ),
			) ),
			array( 'FLEXIBEL BLEIBEN', 'Pausieren, Tarif wechseln oder Zusatzleistungen buchen: Viele Anliegen erledigst du direkt über die App. Bei persönlichen Fragen hilft dir unser Support-Team weiter.', array(), array( 'Für Studierende & Unternehmen', 'Für Studierende, Auszubildende und Firmen bieten wir separate Konditionen. Sprich uns an und wir prüfen, welches Modell passt.' ) ),
		),
		'cta_title' => 'FINDE DEINEN TARIF',
	),
	array(
		'title' => 'Preise', 'slug' => 'preise', 'image' => 'membership', 'eyebrow' => 'KLAR. FAIR. FLEXIBEL.',
		'lead' => 'Drei Tarife, transparente Leistungen und kein Kleingedrucktes, das deinen Start kompliziert macht.',
		'meta' => 'Uptown Fitness Preise ab 29,90 Euro monatlich. Vergleiche Flex, Smart und Complete inklusive 24/7 Zugang, App und Coaching.',
		'intro' => 'Wähle deine Laufzeit und den Umfang der Begleitung. Alle Tarife enthalten 24/7 Zugang, die Uptown App und alle regulären Trainingszonen deines Home Clubs.',
		'sections' => array(
			array( 'DEINE TARIFE', 'Die Preise dienen als transparente Orientierung. Verfügbare Aktionen und regionale Konditionen werden im jeweiligen Club angezeigt.', array(
				array( 'FLEX · 39,90 €', 'Monatlich kündbar, Home-Club-Zugang, App und digitaler Startplan.' ),
				array( 'SMART · 29,90 €', '12 Monate Laufzeit, Home-Club-Zugang, App, Check-up und Startplan.' ),
				array( 'COMPLETE · 49,90 €', 'Alle Clubs, Kurse, monatlicher Coach-Check und erweiterte App-Analysen.' ),
			) ),
			array( 'OHNE ÜBERRASCHUNGEN', 'Einmalig fällt eine Startpauschale von 29 Euro für Aktivierung, Club-Einweisung und deinen ersten Trainingsplan an. Getränke, Personal Training und spezielle Workshops können optional ergänzt werden.', array(), array( 'Risikofrei testen', 'Nutze zuerst dein kostenloses Probetraining. So lernst du Club, Ausstattung und Atmosphäre kennen, bevor du dich entscheidest.' ) ),
		),
	),
	array(
		'title' => 'Standorte', 'slug' => 'standorte', 'image' => 'facility', 'eyebrow' => 'SMART. NAH. FÜR DICH.',
		'lead' => 'Finde deinen Uptown Club und sieh Öffnung, Ausstattung und Auslastung auf einen Blick.',
		'meta' => 'Finde Uptown Fitness Standorte in deiner Nähe. Alle Clubs bieten moderne Trainingszonen, 24/7 Zugang und digitale Auslastungsanzeige.',
		'intro' => 'Unsere Clubs liegen dort, wo dein Alltag stattfindet: gut erreichbar, klar ausgestattet und rund um die Uhr geöffnet. Wähle deinen Home Club oder trainiere mit Complete flexibel an allen Standorten.',
		'sections' => array(
			array( 'UNSERE FEATURED CLUBS', 'Jeder Club folgt denselben Qualitätsstandards und hat zusätzlich einen eigenen lokalen Charakter.', array(
				array( 'Berlin Mitte', 'Alexanderstraße 18 · Strength, Cardio, Functional · 24/7 geöffnet' ),
				array( 'Hamburg Altona', 'Neue Große Bergstraße 44 · Strength, Mobility, Classes · 24/7 geöffnet' ),
				array( 'Köln Ehrenfeld', 'Venloer Straße 312 · Strength, Functional, Recovery · 24/7 geöffnet' ),
			) ),
			array( 'DEIN CLUB IMMER DABEI', 'In der App findest du Anfahrt, Ausstattung, aktuelle Auslastung und Club-News. Wenn du den Complete Tarif nutzt, wechselst du spontan zwischen allen verfügbaren Uptown Standorten.', array(), array( 'Neue Clubs', 'Berlin Kreuzberg, Düsseldorf Bilk und Frankfurt Ostend befinden sich in Vorbereitung. Registriere dich für Club-Updates und sichere dir Eröffnungsangebote.' ) ),
		),
	),
	array(
		'title' => 'Kurse', 'slug' => 'kurse', 'image' => 'yoga', 'eyebrow' => 'ENERGIE, DIE ANSTECKT',
		'lead' => 'Kleine Gruppen, klare Formate und Coaches, die dich motivieren, ohne dich zu überfordern.',
		'meta' => 'Uptown Fitness Kurse: HIIT, Strength, Cycling, Yoga und Mobility für jedes Level. Kursplan ansehen und über die App buchen.',
		'intro' => 'Gemeinsames Training bringt Struktur und neue Energie. Unsere Kurse sind so aufgebaut, dass Einsteigerinnen und Fortgeschrittene sinnvoll nebeneinander trainieren können.',
		'sections' => array(
			array( 'DEIN FORMAT', 'Jeder Kurs hat einen klaren Schwerpunkt. Die Intensität lässt sich über Varianten, Tempo und Widerstand an dein Level anpassen.', array(
				array( 'Uptown HIIT', '45 Minuten intensive Intervalle für Kondition, Kraft und mentale Energie.' ),
				array( 'Power Strength', 'Technisch sauberes Ganzkörpertraining mit Langhantel und freien Gewichten.' ),
				array( 'Ride', 'Rhythmus, Widerstand und Ausdauer auf dem Bike – motivierend und fokussiert.' ),
				array( 'Yoga Flow', 'Fließende Sequenzen für Kraft, Balance und bewusstes Atmen.' ),
				array( 'Mobility Reset', 'Beweglichkeit und aktive Erholung für Alltag und Training.' ),
				array( 'Core Lab', 'Kontrollierte Rumpfarbeit für Stabilität und belastbare Bewegung.' ),
			) ),
			array( 'SO FUNKTIONIERT DIE BUCHUNG', 'Öffne den Kursplan in der Uptown App, filtere nach Club und Format und reserviere deinen Platz. Bis zwei Stunden vor Beginn kannst du kostenfrei stornieren.', array(), array( 'Erster Kurs?', 'Komm zehn Minuten früher und sprich den Coach an. Du erhältst eine kurze Einführung und passende Übungsvarianten.' ) ),
		),
	),
	array(
		'title' => 'Probetraining', 'slug' => 'probetraining', 'image' => 'woman', 'eyebrow' => 'KOSTENLOS TESTEN',
		'lead' => 'Lerne Club, Equipment und Atmosphäre kennen – unverbindlich und ohne Verkaufsdruck.',
		'meta' => 'Kostenloses Probetraining bei Uptown Fitness buchen. Clubtour, persönlicher Start-Check und Zugang zu allen Trainingszonen inklusive.',
		'intro' => 'Ein Gym muss sich richtig anfühlen. Deshalb kannst du Uptown Fitness vor deiner Entscheidung in Ruhe testen. Plane etwa 60 bis 90 Minuten ein und bring Sportkleidung, saubere Schuhe und ein Handtuch mit.',
		'sections' => array(
			array( 'DEIN ERSTER BESUCH', 'Wir halten den Einstieg bewusst einfach und konzentrieren uns auf das, was für dich relevant ist.', array(
				array( '01 · Ankommen', 'Kurzer Check-in, Zielabfrage und Erklärung des Ablaufs.' ),
				array( '02 · Entdecken', 'Geführte Clubtour mit Einblick in alle Trainingszonen.' ),
				array( '03 · Trainieren', 'Freies Testtraining oder kompakter Coach-Check.' ),
			) ),
			array( 'GUT VORBEREITET', 'Buche deinen Termin online. Du erhältst sofort eine Bestätigung mit Clubadresse und Checkliste. Wenn du gesundheitliche Einschränkungen hast, teile sie uns bitte vor dem Training mit.', array(), array( 'Ohne Verpflichtung', 'Nach dem Training erklären wir dir auf Wunsch die Tarife. Du musst dich nicht sofort entscheiden und erhältst keine aufdringlichen Nachfassanrufe.' ) ),
		),
		'cta_title' => 'JETZT TERMIN SICHERN',
		'cta_text' => 'Wähle deinen Club und deinen Wunschtermin. Wir kümmern uns um den Rest.',
	),
	array(
		'title' => 'Uptown App', 'slug' => 'uptown-app', 'image' => 'cardio', 'eyebrow' => 'DEIN CLUB IN DER TASCHE',
		'lead' => 'Zugang, Auslastung, Trainingsplan und Support – alles in einer klaren App.',
		'meta' => 'Die Uptown Fitness App verbindet Check-in, Live-Auslastung, Trainingspläne, Kursbuchung und Fortschritt auf deinem Smartphone.',
		'intro' => 'Technologie soll dein Training vereinfachen, nicht ablenken. Die Uptown App bündelt die wichtigsten Funktionen und bleibt dabei bewusst übersichtlich.',
		'sections' => array(
			array( 'SMART TRAINIEREN', 'Vom Weg zum Club bis zum letzten Satz sparst du Zeit und behältst den Überblick.', array(
				array( 'Digitaler Check-in', 'Öffne den Club sicher mit deinem persönlichen mobilen Zugang.' ),
				array( 'Live-Auslastung', 'Sieh, wann dein Club entspannt, normal oder stark besucht ist.' ),
				array( 'Trainingsplan', 'Übungen, Sätze und Gewichte sind übersichtlich gespeichert.' ),
				array( 'Kurse buchen', 'Reserviere Plätze, verwalte Termine und erhalte Erinnerungen.' ),
				array( 'Fortschritt sehen', 'Verfolge Regelmäßigkeit, Trainingsvolumen und persönliche Bestwerte.' ),
				array( 'Support erreichen', 'Klare Hilfe für Mitgliedschaft, Technik und Clubfragen.' ),
			) ),
			array( 'DEINE DATEN, DEINE KONTROLLE', 'Du entscheidest, welche Trainingsdaten du erfassen möchtest. Persönliche Daten werden zweckgebunden verarbeitet und nicht zu Werbezwecken verkauft.', array(), array( 'iOS & Android', 'Die Uptown App ist für aktuelle iOS- und Android-Geräte verfügbar. Nach deiner Anmeldung erhältst du den Download-Link automatisch.' ) ),
		),
	),
	array(
		'title' => 'Ernährungscoaching', 'slug' => 'ernaehrungscoaching', 'image' => 'nutrition', 'eyebrow' => 'ERNÄHRUNG, DIE FUNKTIONIERT',
		'lead' => 'Alltagstaugliche Orientierung statt Verbote, Trends und kurzfristiger Diäten.',
		'meta' => 'Individuelles Ernährungscoaching bei Uptown Fitness: realistische Gewohnheiten, fundierte Beratung und Strategien für Training und Alltag.',
		'intro' => 'Gute Ernährung muss nicht perfekt sein. Sie sollte zu deinem Tagesablauf, deinen Vorlieben und deinem Trainingsziel passen. Unser Coaching hilft dir, Zusammenhänge zu verstehen und realistische Gewohnheiten aufzubauen.',
		'sections' => array(
			array( 'DEIN PLAN FÜR DEN ALLTAG', 'Wir starten mit einer strukturierten Bestandsaufnahme und konzentrieren uns auf wenige Veränderungen mit großer Wirkung.', array(
				array( 'Analyse', 'Ziele, Essrhythmus, Alltag, Training und bisherige Erfahrungen.' ),
				array( 'Strategie', 'Klare Prioritäten für Protein, Gemüse, Energiezufuhr und Flüssigkeit.' ),
				array( 'Umsetzung', 'Einkauf, Meal Prep, Restaurantbesuche und flexible Lösungen.' ),
			) ),
			array( 'OHNE SCHWARZ-WEISS-DENKEN', 'Einzelne Lebensmittel entscheiden nicht über deinen Fortschritt. Wir betrachten Muster über Wochen und entwickeln Lösungen, die auch an stressigen Tagen funktionieren.', array(), array( 'Wichtiger Hinweis', 'Unser Coaching ersetzt keine medizinische Ernährungsberatung. Bei Erkrankungen, Allergien oder Essstörungen arbeiten wir mit entsprechend qualifizierten Fachstellen zusammen.' ) ),
		),
	),
	array(
		'title' => 'Recovery & Regeneration', 'slug' => 'recovery-regeneration', 'image' => 'recovery', 'eyebrow' => 'FORTSCHRITT BRAUCHT PAUSEN',
		'lead' => 'Mobility, Schlaf und aktive Erholung sind keine Extras, sondern Teil deines Trainings.',
		'meta' => 'Recovery bei Uptown Fitness: Mobility, aktive Regeneration, Massageangebote und praktische Strategien für bessere Erholung und Leistung.',
		'intro' => 'Training setzt den Reiz. Anpassung findet in der Erholung statt. Deshalb zeigen wir dir, wie du Belastung und Pause sinnvoll aufeinander abstimmst.',
		'sections' => array(
			array( 'BESSER ERHOLEN', 'Unsere Recovery-Angebote ergänzen dein Training und helfen dir, beweglich und belastbar zu bleiben.', array(
				array( 'Mobility Zone', 'Rollen, Bänder und Beweglichkeitsroutinen für Warm-up und Cool-down.' ),
				array( 'Recovery Sessions', 'Geführte Einheiten mit Fokus auf Atmung, Beweglichkeit und Entspannung.' ),
				array( 'Massage Partner', 'Buchbare Sportmassagen an ausgewählten Standorten.' ),
			) ),
			array( 'REGENERATION PLANEN', 'Ausreichend Schlaf, sinnvoll verteilte Trainingstage und passende Intensitäten verhindern, dass Motivation in Erschöpfung kippt. Unsere Coaches helfen dir bei der Wochenplanung.', array(), array( 'Schmerz ist kein Trainingsziel', 'Akute oder anhaltende Schmerzen sollten medizinisch abgeklärt werden. Wir passen Übungen an, stellen aber keine Diagnosen.' ) ),
		),
	),
	array(
		'title' => 'Firmenfitness', 'slug' => 'firmenfitness', 'image' => 'community', 'eyebrow' => 'STARKE TEAMS',
		'lead' => 'Flexible Fitnesslösungen, die Mitarbeitende wirklich nutzen – einfach verwaltet und messbar.',
		'meta' => 'Uptown Firmenfitness: flexible Gym-Mitgliedschaften, Team-Challenges und Gesundheitsangebote für Unternehmen jeder Größe.',
		'intro' => 'Gesunde Mitarbeitende brauchen mehr als einen Gutschein, der in der Schublade liegt. Unser Firmenfitness-Modell verbindet flexible Clubzugänge mit aktivierenden Formaten und einer einfachen Verwaltung.',
		'sections' => array(
			array( 'ANGEBOTE FÜR UNTERNEHMEN', 'Wähle einzelne Bausteine oder kombiniere sie zu einem eigenen Programm.', array(
				array( 'Corporate Membership', 'Vergünstigte Mitgliedschaften mit monatlicher Sammelabrechnung.' ),
				array( 'Team Challenges', 'Niedrigschwellige Bewegungsziele, die Zusammenarbeit fördern.' ),
				array( 'Workshops', 'Praxisnahe Sessions zu Training, Ergonomie, Energie und Recovery.' ),
			) ),
			array( 'EINFACH STARTEN', 'Nach einem kurzen Bedarfsgespräch erhältst du ein transparentes Angebot. Wir stellen Kommunikationsmaterial bereit und begleiten den Rollout auf Wunsch mit einem digitalen Kick-off.', array(), array( 'Datenschutz', 'Unternehmen erhalten ausschließlich aggregierte Nutzungsinformationen. Persönliche Trainings- und Gesundheitsdaten bleiben privat.' ) ),
		),
	),
	array(
		'title' => 'Trainer-Team', 'slug' => 'trainer-team', 'image' => 'trainer', 'eyebrow' => 'MENSCHEN, DIE DICH SEHEN',
		'lead' => 'Unsere Coaches verbinden Fachwissen mit klarer Kommunikation und echter Aufmerksamkeit.',
		'meta' => 'Lerne das Uptown Fitness Trainer-Team kennen: qualifizierte Coaches für Kraft, Ausdauer, Mobility, Personal Training und gesunde Routinen.',
		'intro' => 'Gutes Coaching macht dich nicht abhängig. Es hilft dir, Bewegung zu verstehen, Sicherheit aufzubauen und Schritt für Schritt selbstständiger zu trainieren.',
		'sections' => array(
			array( 'SO ARBEITEN WIR', 'Unsere Coaches werden nach Qualifikation, Praxiserfahrung und Kommunikationsstärke ausgewählt. Regelmäßige Schulungen halten das Wissen aktuell.', array(
				array( 'Verständlich', 'Klare Hinweise statt unnötiger Fachsprache und komplizierter Erklärungen.' ),
				array( 'Aufmerksam', 'Wir beobachten Technik und Belastung, ohne dein Training ständig zu unterbrechen.' ),
				array( 'Realistisch', 'Pläne orientieren sich an deinem Alltag und nicht an idealisierten Wochen.' ),
			) ),
			array( 'PERSONAL TRAINING', 'Wenn du intensivere Begleitung möchtest, kannst du persönliche Sessions buchen. Nach Anamnese und Zielgespräch entsteht ein Plan, der regelmäßig überprüft und angepasst wird.', array(), array( 'Coaches im Club', 'Zu betreuten Servicezeiten sind Coaches auf der Fläche ansprechbar. Die genauen Zeiten findest du in der App und auf der Standortseite.' ) ),
		),
	),
	array(
		'title' => 'Erfolgsgeschichten', 'slug' => 'erfolgsgeschichten', 'image' => 'woman', 'eyebrow' => 'ECHTER FORTSCHRITT',
		'lead' => 'Keine Vorher-nachher-Show. Sondern ehrliche Geschichten über Routinen, Mut und kleine Schritte.',
		'meta' => 'Uptown Fitness Erfolgsgeschichten: Mitglieder erzählen von mehr Kraft, besserer Gesundheit und Routinen, die wirklich bleiben.',
		'intro' => 'Fortschritt sieht für jeden Menschen anders aus. Manchmal ist es ein neuer persönlicher Rekord. Manchmal der erste schmerzfreie Spaziergang seit Monaten. Und manchmal einfach die Erkenntnis: Ich bin wieder regelmäßig da.',
		'sections' => array(
			array( 'DREI WEGE, DREI ZIELE', 'Unsere Mitglieder teilen ihre Erfahrungen, damit andere sehen: Veränderung beginnt nicht mit Perfektion.', array(
				array( 'Mara · 34', 'Von unregelmäßigen Workouts zu drei festen Trainingstagen und deutlich mehr Energie im Alltag.' ),
				array( 'Jonas · 46', 'Mit Techniktraining und Mobility zurück zu schmerzarmen Grundübungen.' ),
				array( 'Elif · 27', 'Durch Krafttraining mehr Selbstvertrauen und den ersten sauberen Klimmzug geschafft.' ),
			) ),
			array( 'WAS ALLE VERBINDET', 'Keiner dieser Wege verlief geradlinig. Urlaube, Stress und Pausen gehörten dazu. Entscheidend war, nach Unterbrechungen nicht bei null zu beginnen, sondern an die eigene Erfahrung anzuknüpfen.', array(), array( 'Deine Geschichte zählt', 'Wenn du deinen Fortschritt teilen möchtest, melde dich bei unserem Community-Team. Veröffentlichung erfolgt selbstverständlich nur mit deiner ausdrücklichen Zustimmung.' ) ),
		),
		'quote' => 'Ich habe aufgehört, auf Motivation zu warten. Jetzt habe ich eine Routine, die auch an normalen Tagen funktioniert.',
	),
	array(
		'title' => 'Community', 'slug' => 'community', 'image' => 'community', 'eyebrow' => 'GEMEINSAM EINFACHER',
		'lead' => 'Eine offene Community, in der unterschiedliche Ziele nebeneinander Platz haben.',
		'meta' => 'Uptown Community: gemeinsame Challenges, Club-Events, Workshops und ein respektvoller Ort für Fitness auf jedem Level.',
		'intro' => 'Du trainierst für dich – aber nicht allein. Die Uptown Community schafft Begegnungen, Motivation und Austausch, ohne dass daraus Leistungsdruck entsteht.',
		'sections' => array(
			array( 'SO KOMMEN WIR ZUSAMMEN', 'Unsere Formate sind freiwillig, offen und bewusst niedrigschwellig.', array(
				array( 'Club Challenges', 'Gemeinsame Bewegungsziele, bei denen Regelmäßigkeit mehr zählt als Bestleistung.' ),
				array( 'Open Workouts', 'Kostenlose Trainingstreffen mit Coach-Impulsen und Zeit für Austausch.' ),
				array( 'Member Nights', 'Musik, Snacks, Mini-Workshops und ein entspannter Blick hinter die Kulissen.' ),
			) ),
			array( 'RESPEKT ALS BASIS', 'Diskriminierung, Belästigung und einschüchterndes Verhalten haben bei Uptown keinen Platz. Unsere Clubregeln gelten für alle und unser Team ist bei Problemen jederzeit ansprechbar.', array(), array( 'Mitgestalten', 'Du hast eine Idee für ein Format, einen Workshop oder eine lokale Kooperation? Schreib unserem Community-Team.' ) ),
		),
	),
	array(
		'title' => 'Events', 'slug' => 'events', 'image' => 'functional', 'eyebrow' => 'MEHR BEWEGUNG',
		'lead' => 'Workshops, Challenges und gemeinsame Sessions bringen Abwechslung in deine Routine.',
		'meta' => 'Uptown Fitness Events: Technik-Workshops, Community Workouts, Laufgruppen und Challenges. Entdecke aktuelle Termine.',
		'intro' => 'Unsere Events ergänzen dein Training mit neuem Wissen, gemeinsamer Energie und unkomplizierten Begegnungen. Die meisten Termine sind für Mitglieder kostenfrei.',
		'sections' => array(
			array( 'NÄCHSTE TERMINE', 'Die konkreten Zeiten findest du in der App. Dort kannst du deinen Platz direkt reservieren.', array(
				array( 'Squat Lab', 'Technik-Workshop für sichere Kniebeugen · Berlin Mitte · 90 Minuten.' ),
				array( 'Sunday Run Club', 'Lockerer Community Run in zwei Pace-Gruppen · Hamburg Altona.' ),
				array( 'Mobility Sunday', 'Geführte Recovery Session · Köln Ehrenfeld · für jedes Level.' ),
			) ),
			array( 'DEIN EVENT, DEIN LEVEL', 'In jeder Beschreibung findest du Zielgruppe, Intensität und benötigte Erfahrung. Bei Unsicherheit hilft dir das Club-Team bei der Auswahl.', array(), array( 'Externe Gäste', 'Ausgewählte Community Events sind auch für Nichtmitglieder geöffnet. Die Kennzeichnung findest du direkt beim Termin.' ) ),
		),
	),
	array(
		'title' => 'Häufige Fragen', 'slug' => 'faq', 'image' => 'facility', 'eyebrow' => 'KLARE ANTWORTEN',
		'lead' => 'Alles Wichtige zu Zugang, Mitgliedschaft, Training, Kursen und Support.',
		'meta' => 'Uptown Fitness FAQ: Antworten zu 24/7 Zugang, Mitgliedschaft, Kündigung, Probetraining, Kursen, App und Ausstattung.',
		'intro' => 'Hier findest du Antworten auf die häufigsten Fragen. Für persönliche Anliegen erreichst du uns über die Kontaktseite oder direkt in der Uptown App.',
		'sections' => array(
			array( 'MITGLIEDSCHAFT & ZUGANG', '<strong>Wann kann ich trainieren?</strong><br>Mit aktiver Mitgliedschaft hast du täglich rund um die Uhr Zugang. Betreute Servicezeiten unterscheiden sich je Standort.<br><br><strong>Wie funktioniert der Check-in?</strong><br>Du nutzt deinen persönlichen Zugang in der Uptown App. Der Zugang darf nicht an andere Personen weitergegeben werden.<br><br><strong>Kann ich meine Mitgliedschaft pausieren?</strong><br>Je nach Tarif sind Pausen bei längerer Krankheit, Schwangerschaft oder aus beruflichen Gründen möglich. Unser Support prüft deinen Fall individuell.' ),
			array( 'TRAINING & KURSE', '<strong>Ist Uptown für Anfänger geeignet?</strong><br>Ja. Dein Startplan erklärt die wichtigsten Übungen und im Probetraining zeigen wir dir den Club.<br><br><strong>Sind Kurse inklusive?</strong><br>Im Complete Tarif sind reguläre Kurse enthalten. Bei anderen Tarifen können sie flexibel ergänzt werden.<br><br><strong>Kann ich Personal Training buchen?</strong><br>Ja. Einzeltermine und Pakete kannst du über das Club-Team anfragen.' ),
			array( 'VERTRAG & SUPPORT', '<strong>Wie kündige ich?</strong><br>Du kannst deine Kündigung über die App, per E-Mail oder schriftlich einreichen. Es gelten die Fristen deines Tarifs.<br><br><strong>Was passiert bei einem verlorenen Smartphone?</strong><br>Kontaktiere sofort den Support. Wir sperren den mobilen Zugang und helfen bei der Neueinrichtung.<br><br><strong>Gibt es ein Mindestalter?</strong><br>Reguläre Mitgliedschaften sind ab 16 Jahren möglich; unter 18 benötigen wir die Zustimmung einer erziehungsberechtigten Person.' ),
		),
		'cta_title' => 'NOCH EINE FRAGE?',
		'cta_text' => 'Unser Support hilft dir persönlich weiter – schnell, freundlich und ohne Warteschleifen-Marathon.',
	),
	array(
		'title' => 'Kontakt', 'slug' => 'kontakt', 'image' => 'trainer', 'eyebrow' => 'WIR SIND FÜR DICH DA',
		'lead' => 'Fragen zum Club, zur Mitgliedschaft oder zu deinem Start? Schreib uns.',
		'meta' => 'Kontakt zu Uptown Fitness: Support für Mitgliedschaft, Probetraining, Clubfragen und Firmenfitness. Schnell und unkompliziert erreichbar.',
		'form' => true,
		'intro' => 'Viele Anliegen kannst du direkt in der Uptown App erledigen. Wenn du persönliche Hilfe brauchst, erreichst du unser Support-Team per E-Mail oder Telefon.',
		'sections' => array(
			array( 'DEIN DIREKTER KONTAKT', 'E-Mail: <a href="mailto:hallo@uptown-fitness.de">hallo@uptown-fitness.de</a><br>Telefon: <a href="tel:+493055501270">+49 30 555 012 70</a><br>Supportzeiten: Montag bis Freitag, 08:00–20:00 Uhr; Samstag, 10:00–16:00 Uhr.', array(
				array( 'Mitgliedschaft', 'Vertrag, Tarifwechsel, Pause oder Kündigung – bitte Mitgliedsnummer bereithalten.' ),
				array( 'Probetraining', 'Termin, Clubauswahl und Fragen zu deinem ersten Besuch.' ),
				array( 'Firmenfitness', 'Individuelle Angebote für Teams und Unternehmen.' ),
			) ),
			array( 'ANTWORTZEITEN', 'E-Mails beantworten wir in der Regel innerhalb eines Werktags. Bei sicherheitsrelevanten Problemen im Club nutze bitte die gekennzeichnete Notfallsprechstelle vor Ort.', array(), array( 'Presse & Kooperationen', 'Anfragen zu Medien, Partnerschaften und lokalen Projekten sendest du an partners@uptown-fitness.de.' ) ),
		),
		'cta_title' => 'CLUB LIVE ERLEBEN',
	),
	array(
		'title' => 'Karriere', 'slug' => 'karriere', 'image' => 'trainer', 'eyebrow' => 'GROW WITH US',
		'lead' => 'Du liebst Bewegung, klare Kommunikation und echte Gastfreundschaft? Dann sollten wir uns kennenlernen.',
		'meta' => 'Karriere bei Uptown Fitness: Jobs für Coaches, Club Management, Service, Marketing und digitale Produktentwicklung.',
		'intro' => 'Uptown wächst – und sucht Menschen, die Verantwortung übernehmen, offen lernen und Fitness zugänglicher machen möchten.',
		'sections' => array(
			array( 'DEIN PLATZ BEI UPTOWN', 'Unsere Teams arbeiten interdisziplinär und direkt. Ideen werden nicht nach Position bewertet, sondern danach, ob sie Mitgliedern und Clubs helfen.', array(
				array( 'Coaching', 'Trainerinnen und Trainer für Fläche, Kurse und Personal Training.' ),
				array( 'Club Team', 'Service, Operations und Club Management mit Gastgebermentalität.' ),
				array( 'Head Office', 'Produkt, Tech, Marketing, People und Expansion.' ),
			) ),
			array( 'WAS DU ERWARTEN KANNST', 'Faire Entwicklungsgespräche, interne Weiterbildung, kostenlose Mitgliedschaft und transparente Ziele. Bewerbungen sind unabhängig von Herkunft, Geschlecht, Alter, Behinderung, Religion oder Identität willkommen.', array(), array( 'Initiativ bewerben', 'Schick deinen Lebenslauf und ein paar ehrliche Sätze zu deiner Motivation an jobs@uptown-fitness.de.' ) ),
		),
	),
	array(
		'title' => 'Magazin', 'slug' => 'magazin', 'image' => 'nutrition', 'eyebrow' => 'WISSEN, DAS BEWEGT',
		'lead' => 'Fundierte Tipps für Training, Ernährung, Recovery und Routinen – verständlich und alltagstauglich.',
		'meta' => 'Uptown Magazin: verständliche Artikel über Krafttraining, Cardio, Ernährung, Recovery und langfristige Fitnessroutinen.',
		'intro' => 'Im Uptown Magazin übersetzen wir Trainingswissen in konkrete Schritte. Ohne Hype, extreme Versprechen oder unnötige Komplexität.',
		'sections' => array(
			array( 'UNSERE THEMEN', 'Jeder Beitrag wird fachlich geprüft und so geschrieben, dass du die wichtigsten Punkte direkt anwenden kannst.', array(
				array( 'Training', 'Technik, Planung, Progression und sinnvolle Übungsauswahl.' ),
				array( 'Ernährung', 'Praktische Grundlagen für Energie, Leistung und Gesundheit.' ),
				array( 'Recovery', 'Schlaf, Stress, Mobility und intelligentes Belastungsmanagement.' ),
			) ),
			array( 'NEUE ARTIKEL', 'Auf dieser Seite findest du die neuesten Beiträge. Ergänzend teilen wir kurze Impulse und Club-News über unseren Newsletter.', array(), array( 'Kein medizinischer Ersatz', 'Unsere Inhalte dienen der allgemeinen Information und ersetzen keine individuelle medizinische Beratung.' ) ),
		),
	),
	array(
		'title' => 'Datenschutz', 'slug' => 'datenschutz', 'image' => 'facility', 'eyebrow' => 'TRANSPARENT INFORMIERT',
		'lead' => 'Wie wir personenbezogene Daten verarbeiten und welche Rechte du hast.',
		'meta' => 'Datenschutzhinweise von Uptown Fitness zu Website, Mitgliedschaft, App, Kontakt und deinen Rechten.',
		'intro' => 'Der Schutz deiner Daten ist uns wichtig. Diese Musterinformation beschreibt die Grundsätze der Datenverarbeitung auf unserer Website und in unseren digitalen Diensten.',
		'sections' => array(
			array( 'VERANTWORTLICHE STELLE', 'Uptown Fitness GmbH, Alexanderstraße 18, 10178 Berlin, E-Mail: datenschutz@uptown-fitness.de. Daten werden nur verarbeitet, soweit dies zur Vertragserfüllung, Kommunikation, Sicherheit oder aufgrund einer gesetzlichen Pflicht erforderlich ist.' ),
			array( 'DEINE RECHTE', 'Du hast im gesetzlichen Rahmen Rechte auf Auskunft, Berichtigung, Löschung, Einschränkung, Datenübertragbarkeit und Widerspruch. Anfragen richtest du an datenschutz@uptown-fitness.de.' ),
			array( 'COOKIES & ANALYSE', 'Technisch notwendige Cookies sichern grundlegende Funktionen. Optionale Analyse- oder Marketingtechnologien werden nur nach Einwilligung aktiviert. Diese Seite ist ein redaktionelles Muster und sollte vor Veröffentlichung rechtlich geprüft werden.' ),
		),
		'cta_title' => 'FRAGEN ZUM DATENSCHUTZ?',
	),
	array(
		'title' => 'Impressum', 'slug' => 'impressum', 'image' => 'facility', 'eyebrow' => 'RECHTLICHE ANGABEN',
		'lead' => 'Anbieterkennzeichnung und Kontaktinformationen von Uptown Fitness.',
		'meta' => 'Impressum und Anbieterkennzeichnung der Uptown Fitness GmbH.',
		'intro' => '<strong>Uptown Fitness GmbH</strong><br>Alexanderstraße 18<br>10178 Berlin<br>Deutschland',
		'sections' => array(
			array( 'KONTAKT', 'Telefon: +49 30 555 012 70<br>E-Mail: hallo@uptown-fitness.de<br>Vertreten durch die Geschäftsführung: Lena Berger und David König.' ),
			array( 'REGISTER & STEUERN', 'Handelsregister: Amtsgericht Berlin-Charlottenburg, HRB 000000 B<br>Umsatzsteuer-ID gemäß § 27a UStG: DE000000000. Dies sind Musterangaben und müssen vor Veröffentlichung durch reale Unternehmensdaten ersetzt werden.' ),
			array( 'HAFTUNGSHINWEIS', 'Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für Inhalte externer Links. Für Inhalte verlinkter Seiten sind ausschließlich deren Betreiber verantwortlich.' ),
		),
		'cta_title' => 'KONTAKT AUFNEHMEN',
	),
	array(
		'title' => 'Allgemeine Geschäftsbedingungen', 'slug' => 'agb', 'image' => 'facility', 'eyebrow' => 'FAIRE GRUNDLAGEN',
		'lead' => 'Rahmenbedingungen für Mitgliedschaft, Nutzung und digitale Dienste.',
		'meta' => 'Allgemeine Geschäftsbedingungen für Mitgliedschaften und Leistungen von Uptown Fitness.',
		'intro' => 'Diese Muster-AGB geben einen redaktionellen Überblick über wesentliche Vertragsbereiche. Vor geschäftlicher Nutzung müssen sie durch eine qualifizierte Rechtsberatung geprüft und an das tatsächliche Angebot angepasst werden.',
		'sections' => array(
			array( 'MITGLIEDSCHAFT', 'Der Vertrag kommt mit Bestätigung der Anmeldung zustande. Tarif, Laufzeit, Beiträge und enthaltene Leistungen ergeben sich aus der individuellen Vertragsübersicht.' ),
			array( 'ZUGANG & CLUBREGELN', 'Der persönliche Zugang ist nicht übertragbar. Mitglieder beachten Sicherheits-, Hygiene- und Hausregeln sowie Anweisungen des Club-Teams. Grobe oder wiederholte Verstöße können zur Sperrung führen.' ),
			array( 'LAUFZEIT & KÜNDIGUNG', 'Kündigungsfristen und Verlängerungsregeln richten sich nach dem gewählten Tarif und den gesetzlichen Vorgaben. Kündigungen können über die vorgesehenen digitalen oder schriftlichen Wege eingereicht werden.' ),
		),
		'cta_title' => 'NOCH FRAGEN?',
	),
);

$page_ids = array();
foreach ( $pages as $page ) {
	$page_ids[ $page['slug'] ] = uptown_upsert( 'page', $page );
}

$services = array(
	array(
		'title' => 'Krafttraining', 'slug' => 'krafttraining', 'image' => 'weights', 'eyebrow' => 'BUILD YOUR BASE',
		'lead' => 'Mehr Kraft, bessere Technik und ein Trainingsplan, der nachvollziehbar vorankommt.',
		'meta' => 'Krafttraining bei Uptown Fitness mit Freihanteln, Racks, Maschinen und Coach-Support. Für Einsteiger und Fortgeschrittene.',
		'intro' => 'Krafttraining verbessert nicht nur Muskelkraft. Es unterstützt Knochengesundheit, Belastbarkeit und Selbstvertrauen im Alltag. In unseren Strength Zones findest du Platz und Equipment für systematisches Training.',
		'sections' => array(
			array( 'DEINE STRENGTH ZONE', 'Racks, Plattformen, Kurzhanteln, Kabelzüge und geführte Maschinen ermöglichen sinnvolle Progression auf jedem Niveau.', array(
				array( 'Freie Gewichte', 'Für koordinativ anspruchsvolle Grundübungen und vielfältige Bewegungen.' ),
				array( 'Maschinen', 'Stabile Bewegungsführung für gezielte Belastung und leichten Einstieg.' ),
				array( 'Coach Support', 'Technikhinweise, Übungsvarianten und Unterstützung bei der Planung.' ),
			) ),
			array( 'SO WIRST DU STÄRKER', 'Starte mit Bewegungen, die du kontrollieren kannst. Steigere Wiederholungen, Gewicht oder Bewegungsqualität schrittweise und plane ausreichend Erholung zwischen intensiven Einheiten.', array(), array( 'Für Einsteiger', 'Dein Startplan konzentriert sich auf wenige Grundbewegungen. So lernst du Technik, ohne von zu vielen Übungen überfordert zu werden.' ) ),
		),
	),
	array(
		'title' => 'Functional Training', 'slug' => 'functional-training', 'image' => 'functional', 'eyebrow' => 'MOVE BETTER',
		'lead' => 'Athletische Bewegungen für Kraft, Stabilität, Koordination und mehr Leistungsfähigkeit.',
		'meta' => 'Functional Training bei Uptown Fitness: Kettlebells, Schlitten, Ropes und freie Flächen für Athletik, Stabilität und Kondition.',
		'intro' => 'Functional Training verbindet mehrere Bewegungsrichtungen und Muskelgruppen. Du entwickelst Kraft, Ausdauer und Kontrolle in Übungen, die sich an natürlichen Bewegungsmustern orientieren.',
		'sections' => array(
			array( 'FREIRAUM FÜR BEWEGUNG', 'Unsere Functional Areas bieten Schlittenbahnen, Kettlebells, Medizinbälle, Battle Ropes, Boxen und freie Flächen.', array(
				array( 'Push & Pull', 'Schlitten, Ropes und Carries entwickeln Ganzkörperkraft.' ),
				array( 'Jump & Sprint', 'Explosive Varianten fördern Athletik und Reaktionsfähigkeit.' ),
				array( 'Control', 'Einbeinige Übungen und Rotationen verbessern Stabilität.' ),
			) ),
			array( 'QUALITÄT VOR TEMPO', 'Komplexere Bewegungen werden zunächst ruhig gelernt. Erst wenn die Technik stabil bleibt, erhöhen wir Dynamik, Umfang oder Widerstand.', array(), array( 'Im Kurs oder frei', 'Nutze die Fläche selbstständig oder lerne im Functional Circuit neue Übungskombinationen kennen.' ) ),
		),
	),
	array(
		'title' => 'Cardio & Ausdauer', 'slug' => 'cardio-ausdauer', 'image' => 'cardio', 'eyebrow' => 'FIND YOUR PACE',
		'lead' => 'Ausdauertraining mit klaren Daten, abwechslungsreichen Geräten und deinem eigenen Tempo.',
		'meta' => 'Cardio-Training bei Uptown Fitness mit Laufband, Bike, Rower und Stepper. Ausdauer verbessern und Fortschritt smart verfolgen.',
		'intro' => 'Eine gute Ausdauer unterstützt Herz-Kreislauf-Gesundheit, Erholung und Leistungsfähigkeit. Unsere Cardio Decks ermöglichen ruhige Grundlagenarbeit ebenso wie intensive Intervalle.',
		'sections' => array(
			array( 'DEIN CARDIO DECK', 'Laufbänder, Bikes, Rower, Stepper und Crosstrainer bieten unterschiedliche Belastungsformen.', array(
				array( 'Grundlage', 'Gleichmäßiges Tempo für Ausdauer, aktive Erholung und Stressabbau.' ),
				array( 'Intervalle', 'Kurze intensive Phasen mit geplanten Erholungen.' ),
				array( 'Tracking', 'Zeit, Distanz, Leistung und Herzfrequenz übersichtlich beobachten.' ),
			) ),
			array( 'DOSIERUNG MACHT DEN UNTERSCHIED', 'Nicht jede Einheit muss maximal intensiv sein. Kombiniere überwiegend moderate Belastung mit gezielten Intervallen und passe Umfang an dein aktuelles Niveau an.', array(), array( 'Gelenkschonende Alternativen', 'Bike, Rower und Crosstrainer reduzieren Stoßbelastung und sind sinnvolle Alternativen zum Laufen.' ) ),
		),
	),
	array(
		'title' => 'Yoga & Mobility', 'slug' => 'yoga-mobility', 'image' => 'yoga', 'eyebrow' => 'MOVE WITH CONTROL',
		'lead' => 'Mehr Beweglichkeit, Balance und Körpergefühl für Training und Alltag.',
		'meta' => 'Yoga und Mobility bei Uptown Fitness: Kurse und freie Flächen für Beweglichkeit, Stabilität, Atmung und aktive Regeneration.',
		'intro' => 'Beweglichkeit ist die Fähigkeit, Positionen aktiv und kontrolliert zu nutzen. Yoga und Mobility helfen dir, Bewegungsspielraum, Stabilität und Körperwahrnehmung zu entwickeln.',
		'sections' => array(
			array( 'KONTROLLE STATT ZWANG', 'Unsere Formate kombinieren aktive Beweglichkeit, ruhige Positionen, Atmung und Stabilitätsarbeit.', array(
				array( 'Yoga Flow', 'Dynamische Sequenzen verbinden Kraft, Balance und Atmung.' ),
				array( 'Mobility Reset', 'Gezielte Routinen für Hüfte, Schulter und Wirbelsäule.' ),
				array( 'Recovery Flow', 'Ruhige Bewegungen zur aktiven Erholung nach intensiven Tagen.' ),
			) ),
			array( 'FÜR JEDES LEVEL', 'Du brauchst weder Vorerfahrung noch besondere Beweglichkeit. Coaches bieten Varianten und Hilfsmittel, damit du sicher in deinem aktuellen Bewegungsspielraum arbeitest.', array(), array( 'Als Ergänzung', 'Ein bis zwei kurze Mobility-Einheiten pro Woche können dein Kraft- und Ausdauertraining sinnvoll ergänzen.' ) ),
		),
	),
	array(
		'title' => 'Personal Training', 'slug' => 'personal-training', 'image' => 'trainer', 'eyebrow' => 'ONE GOAL. ONE PLAN.',
		'lead' => 'Individuelle Begleitung für klare Ziele, sichere Technik und effiziente Trainingszeit.',
		'meta' => 'Personal Training bei Uptown Fitness: individuelle Analyse, maßgeschneiderter Plan, Technikcoaching und regelmäßige Fortschrittschecks.',
		'intro' => 'Personal Training ist sinnvoll, wenn du konkrete Ziele verfolgst, wenig Zeit hast oder dir bei Technik und Planung mehr Sicherheit wünschst.',
		'sections' => array(
			array( 'DEIN COACHING-PROZESS', 'Jede Zusammenarbeit beginnt mit einem Gespräch und einer Bewegungsanalyse.', array(
				array( 'Analyse', 'Ziel, Trainingshistorie, Alltag, verfügbare Zeit und Einschränkungen.' ),
				array( 'Plan', 'Eine realistische Wochenstruktur mit klaren Übungen und Progressionsregeln.' ),
				array( 'Coaching', 'Direktes Feedback, Anpassungen und nachvollziehbare Erklärungen.' ),
			) ),
			array( 'SELBSTSTÄNDIGER WERDEN', 'Unser Ziel ist nicht, dass du für immer Anleitung brauchst. Du sollst verstehen, warum du etwas trainierst und wie du deinen Plan langfristig selbst steuern kannst.', array(), array( 'Pakete', 'Buche einzelne Techniktermine oder Pakete mit regelmäßiger Begleitung. Preise und Verfügbarkeit unterscheiden sich je Club.' ) ),
		),
	),
	array(
		'title' => 'Gruppenkurse', 'slug' => 'gruppenkurse', 'image' => 'community', 'eyebrow' => 'MOVE TOGETHER',
		'lead' => 'Struktur, Energie und motivierende Coaches in kleinen bis mittelgroßen Gruppen.',
		'meta' => 'Gruppenkurse bei Uptown Fitness: HIIT, Strength, Cycling, Yoga, Core und Mobility. Für jedes Trainingslevel geeignet.',
		'intro' => 'Im Gruppentraining musst du dir keine Einheit zusammenstellen. Komm an, wähle deine Variante und lass dich von Rhythmus, Coach und Gruppe tragen.',
		'sections' => array(
			array( 'VIELFALT MIT SYSTEM', 'Von intensiven Intervallen bis zu ruhiger Mobility findest du Formate für unterschiedliche Tage und Ziele.', array(
				array( 'Intensity', 'HIIT, Ride und Athletic Circuit für Kondition und Energie.' ),
				array( 'Strength', 'Power Strength und Core Lab mit klarer technischer Anleitung.' ),
				array( 'Balance', 'Yoga Flow und Mobility Reset für Kontrolle und Recovery.' ),
			) ),
			array( 'SICHER IN DER GRUPPE', 'Jede Übung wird erklärt und in Varianten angeboten. Sag dem Coach vor Beginn Bescheid, wenn du neu bist oder Einschränkungen hast.', array(), array( 'Buchung', 'Reserviere deinen Platz über die Uptown App. Bei ausgebuchten Kursen aktiviert dich die Warteliste automatisch, sobald ein Platz frei wird.' ) ),
		),
	),
);
foreach ( $services as $service ) {
	uptown_upsert( 'service', $service );
}

$trainers = array(
	array(
		'title' => 'Lea Sommer', 'slug' => 'lea-sommer', 'image' => 'woman', 'eyebrow' => 'STRENGTH & MOBILITY COACH',
		'lead' => 'Lea verbindet strukturiertes Krafttraining mit alltagstauglicher Beweglichkeit.',
		'meta' => 'Lea Sommer ist Uptown Coach für Krafttraining, Mobility und einen sicheren Einstieg ins Gym.',
		'intro' => 'Lea begleitet seit acht Jahren Menschen auf dem Weg zu mehr Kraft und Bewegungssicherheit. Ihre ruhige Art und klare Sprache helfen besonders Einsteigerinnen und Wiedereinsteigern.',
		'sections' => array(
			array( 'SCHWERPUNKTE', 'Kraftgrundlagen, Mobility, Training nach längeren Pausen und technische Arbeit an Kniebeuge, Kreuzheben und Drücken.', array(
				array( 'Qualifikation', 'B-Lizenz Fitnesstraining, Mobility Coach, Fortbildung Training in der Schwangerschaft.' ),
				array( 'Coaching-Stil', 'Ruhig, präzise und lösungsorientiert.' ),
				array( 'Lieblingsübung', 'Front Squat – weil Beweglichkeit und Kraft zusammenarbeiten.' ),
			) ),
			array( 'LEAS ANSATZ', 'Ein guter Plan ist anspruchsvoll genug, um Fortschritt auszulösen, und realistisch genug, um auch in einer vollen Woche stattzufinden.' ),
		),
		'quote' => 'Du musst nicht alles können. Du brauchst nur einen guten nächsten Schritt.',
	),
	array(
		'title' => 'David Okafor', 'slug' => 'david-okafor', 'image' => 'trainer', 'eyebrow' => 'PERFORMANCE COACH',
		'lead' => 'David coacht Kraft, Athletik und Kondition mit Energie und einem klaren System.',
		'meta' => 'David Okafor ist Uptown Performance Coach für Kraft, Functional Training und athletische Entwicklung.',
		'intro' => 'David kommt aus der Leichtathletik und arbeitet seit zehn Jahren im Performance Training. Er liebt messbaren Fortschritt, achtet dabei aber konsequent auf Technik und Belastungssteuerung.',
		'sections' => array(
			array( 'SCHWERPUNKTE', 'Athletik, Functional Training, Muskelaufbau und Vorbereitung auf sportliche Herausforderungen.', array(
				array( 'Qualifikation', 'Sportwissenschaft B.Sc., Athletiktrainer, Kettlebell Instructor.' ),
				array( 'Coaching-Stil', 'Energetisch, direkt und datenorientiert.' ),
				array( 'Lieblingsübung', 'Farmer Carry – einfach, ehrlich und extrem vielseitig.' ),
			) ),
			array( 'DAVIDS ANSATZ', 'Intensität ist ein Werkzeug. Sie wirkt dann am besten, wenn Technik, Erholung und Wochenplanung stimmen.' ),
		),
		'quote' => 'Trainiere hart, wenn es sinnvoll ist – und smart an allen anderen Tagen.',
	),
	array(
		'title' => 'Mina Yilmaz', 'slug' => 'mina-yilmaz', 'image' => 'yoga', 'eyebrow' => 'YOGA & RECOVERY COACH',
		'lead' => 'Mina bringt Atmung, Beweglichkeit und aktive Erholung in einen modernen Trainingskontext.',
		'meta' => 'Mina Yilmaz ist Uptown Coach für Yoga, Mobility, Recovery und nachhaltige Belastungssteuerung.',
		'intro' => 'Mina unterrichtet Yoga und Mobility seit sieben Jahren. Ihr Fokus liegt nicht auf spektakulären Positionen, sondern auf Bewegung, die im Alltag und beim Krafttraining spürbar hilft.',
		'sections' => array(
			array( 'SCHWERPUNKTE', 'Yoga Flow, Mobility, Atmung, aktive Erholung und Stressmanagement.', array(
				array( 'Qualifikation', '500h Yoga Teacher, Mobility Specialist, Breathwork Foundations.' ),
				array( 'Coaching-Stil', 'Aufmerksam, undogmatisch und verständlich.' ),
				array( 'Lieblingsübung', '90/90 Hip Switch – simpel, kontrolliert und effektiv.' ),
			) ),
			array( 'MINAS ANSATZ', 'Beweglichkeit ist kein Wettbewerb. Entscheidend ist, dass du Positionen kontrollieren und ohne Angst nutzen kannst.' ),
		),
		'quote' => 'Erholung ist nicht das Gegenteil von Training. Sie ist ein Teil davon.',
	),
);
foreach ( $trainers as $trainer ) {
	uptown_upsert( 'trainer', $trainer );
}

$articles = array(
	array(
		'title' => 'Krafttraining für Einsteiger: Dein klarer Start', 'slug' => 'krafttraining-fuer-einsteiger', 'image' => 'weights',
		'lead' => 'Die wichtigsten Grundsätze für sichere Technik, sinnvolle Übungen und Fortschritt ohne Überforderung.',
		'meta' => 'Krafttraining für Einsteiger: Übungen auswählen, Trainingsplan strukturieren und Gewicht sicher steigern. Verständlicher Guide.',
		'intro' => 'Der Einstieg ins Krafttraining muss nicht kompliziert sein. Drei Einheiten mit je fünf gut gewählten Bewegungen reichen aus, um Technik zu lernen und eine belastbare Routine aufzubauen.',
		'sections' => array(
			array( 'STARTE MIT BEWEGUNGSMUSTERN', 'Denke weniger in einzelnen Muskeln und mehr in Bewegungen: Kniebeuge, Hüftbeuge, Drücken, Ziehen und Tragen. Für jedes Muster gibt es einfache Varianten.', array(
				array( 'Wähle Kontrolle', 'Nutze ein Gewicht, das du über den gesamten Bewegungsweg sicher kontrollierst.' ),
				array( 'Lass Wiederholungen übrig', 'Beende den Satz, bevor Technik und Tempo sichtbar zerfallen.' ),
				array( 'Dokumentiere', 'Notiere Gewicht und Wiederholungen, damit Fortschritt planbar wird.' ),
			) ),
			array( 'EIN EINFACHER WOCHENPLAN', 'Trainiere zwei- bis dreimal pro Woche den ganzen Körper. Plane zwischen intensiven Einheiten mindestens einen Erholungstag ein. Wenn du alle Wiederholungen sauber schaffst, erhöhe das Gewicht in kleinen Schritten.', array(), array( 'Das Wichtigste', 'Regelmäßigkeit schlägt den perfekten Plan. Eine einfache Einheit, die du über Monate ausführst, wirkt besser als ein komplexes Programm, das du nach zwei Wochen abbrichst.' ) ),
		),
	),
	array(
		'title' => 'Zone 2 Cardio: Warum ruhiges Tempo wirkt', 'slug' => 'zone-2-cardio', 'image' => 'cardio',
		'lead' => 'Wie moderates Ausdauertraining Fitness, Erholung und Belastbarkeit unterstützt.',
		'meta' => 'Zone 2 Cardio verständlich erklärt: Intensität erkennen, Training planen und Ausdauer ohne maximale Belastung verbessern.',
		'intro' => 'Nicht jedes Cardiotraining muss dich vollständig erschöpfen. Zone 2 beschreibt eine moderate Intensität, bei der du noch in kurzen Sätzen sprechen kannst.',
		'sections' => array(
			array( 'DIE RICHTIGE INTENSITÄT', 'Herzfrequenzbereiche sind individuell. Praktisch hilft der Talk-Test: Die Atmung ist deutlich schneller, ein kurzes Gespräch bleibt aber möglich.', array(
				array( 'Dauer', 'Beginne mit 25 bis 35 Minuten und steigere schrittweise.' ),
				array( 'Häufigkeit', 'Zwei Einheiten pro Woche ergänzen Krafttraining sinnvoll.' ),
				array( 'Gerät', 'Bike, Laufband, Rower oder Crosstrainer – wähle, was du gern nutzt.' ),
			) ),
			array( 'WARUM ES FUNKTIONIERT', 'Moderate Ausdauerarbeit verbessert die Fähigkeit, Energie aerob bereitzustellen. Sie ist gut dosierbar und erzeugt meist weniger Ermüdung als intensive Intervalle.', array(), array( 'Kombination', 'Nutze ruhiges Cardio als Basis und ergänze einzelne Intervalleinheiten, wenn Erholung und Trainingsziel dazu passen.' ) ),
		),
	),
	array(
		'title' => 'Protein im Alltag: Einfach statt kompliziert', 'slug' => 'protein-im-alltag', 'image' => 'nutrition',
		'lead' => 'Praktische Wege, deine Mahlzeiten proteinreicher zu gestalten – ohne jeden Bissen zu tracken.',
		'meta' => 'Protein im Alltag: einfache Quellen, Portionsideen und Meal-Prep-Tipps für Training, Sättigung und Regeneration.',
		'intro' => 'Protein unterstützt Erhalt und Aufbau von Muskelmasse. Du brauchst dafür weder Spezialprodukte noch einen perfekten Ernährungsplan.',
		'sections' => array(
			array( 'PROTEINQUELLEN KOMBINIEREN', 'Plane zu jeder Hauptmahlzeit eine klare Proteinquelle. Geeignet sind unter anderem Joghurt, Quark, Eier, Fisch, mageres Fleisch, Tofu, Tempeh, Hülsenfrüchte und Seitan.', array(
				array( 'Frühstück', 'Joghurt oder Sojaquark mit Haferflocken, Beeren und Nüssen.' ),
				array( 'Mittag', 'Bowl mit Linsen, Gemüse, Reis und einem proteinreichen Topping.' ),
				array( 'Abend', 'Tofu, Fisch oder Hühnchen mit Gemüse und Kartoffeln.' ),
			) ),
			array( 'MENGE INDIVIDUELL BETRACHTEN', 'Der Bedarf hängt von Körpergewicht, Aktivität und Ziel ab. Statt dich an einer Zahl festzuhalten, beginne mit einer Proteinquelle pro Mahlzeit und beobachte Sättigung und Verträglichkeit.', array(), array( 'Supplements', 'Proteinshakes können praktisch sein, sind aber kein Muss. Vollwertige Lebensmittel bleiben die Basis.' ) ),
		),
	),
	array(
		'title' => 'Besser schlafen, besser trainieren', 'slug' => 'besser-schlafen-besser-trainieren', 'image' => 'recovery',
		'lead' => 'Wie Schlaf deine Leistung beeinflusst und welche Routinen wirklich alltagstauglich sind.',
		'meta' => 'Schlaf und Training: praktische Routinen für bessere Regeneration, konstante Leistung und mehr Energie im Alltag.',
		'intro' => 'Schlaf beeinflusst Konzentration, Leistungsbereitschaft, Hungerregulation und Erholung. Trotzdem wird er oft erst beachtet, wenn Müdigkeit das Training bereits ausbremst.',
		'sections' => array(
			array( 'EINE STABILE BASIS', 'Der größte Hebel ist ein verlässlicher Rhythmus. Ähnliche Schlaf- und Aufstehzeiten unterstützen deinen Körper dabei, zur passenden Zeit müde und wach zu werden.', array(
				array( 'Licht', 'Morgens Tageslicht suchen und abends sehr helles Licht reduzieren.' ),
				array( 'Koffein', 'Am späten Nachmittag und Abend möglichst darauf verzichten.' ),
				array( 'Abschalten', 'Eine kurze wiederkehrende Routine markiert den Übergang zur Nacht.' ),
			) ),
			array( 'TRAINING ANPASSEN', 'Nach einer schlechten Nacht musst du nicht automatisch pausieren. Reduziere bei Bedarf Gewicht oder Volumen und konzentriere dich auf saubere Bewegung. Mehrere schlechte Nächte in Folge sind ein Signal, Erholung zu priorisieren.', array(), array( 'Realistisch bleiben', 'Perfekte Schlafhygiene ist kein Ziel. Suche zwei Veränderungen, die sich in deinem Alltag zuverlässig wiederholen lassen.' ) ),
		),
	),
	array(
		'title' => 'Mobility vor dem Training: Was du wirklich brauchst', 'slug' => 'mobility-vor-dem-training', 'image' => 'yoga',
		'lead' => 'Ein kurzes Warm-up, das dich vorbereitet, statt dich schon vor dem ersten Satz zu ermüden.',
		'meta' => 'Mobility vor dem Training: kurzes dynamisches Warm-up für Beweglichkeit, Kontrolle und sichere Kraftübungen.',
		'intro' => 'Ein Warm-up sollte deine Körpertemperatur erhöhen, relevante Bewegungen vorbereiten und dir Rückmeldung geben, wie sich dein Körper heute anfühlt.',
		'sections' => array(
			array( 'DREI PHASEN', 'Beginne allgemein, werde dann bewegungsspezifisch und nähere dich schließlich deinem Trainingsgewicht.', array(
				array( 'Allgemein', 'Drei bis fünf Minuten lockeres Bike, Rudern oder Gehen.' ),
				array( 'Dynamisch', 'Kontrollierte Bewegungen für die Gelenke, die du gleich belastest.' ),
				array( 'Spezifisch', 'Mehrere leichte Vorbereitungssätze deiner ersten Hauptübung.' ),
			) ),
			array( 'WENIGER IST OFT MEHR', 'Lange Stretching-Routinen sind nicht vor jeder Einheit nötig. Nutze nur Übungen, die dir für die konkrete Bewegung mehr Kontrolle oder Komfort geben.', array(), array( 'Individuell testen', 'Wenn eine Position regelmäßig eingeschränkt oder schmerzhaft ist, lass sie fachlich beurteilen statt sie aggressiv zu erzwingen.' ) ),
		),
	),
	array(
		'title' => 'Trainingsplateau: Fünf sinnvolle nächste Schritte', 'slug' => 'trainingsplateau-loesen', 'image' => 'functional',
		'lead' => 'Wenn Fortschritt stagniert, helfen Daten und kleine Anpassungen besser als ein kompletter Neustart.',
		'meta' => 'Trainingsplateau überwinden: fünf praktische Schritte zu Technik, Volumen, Intensität, Ernährung und Erholung.',
		'intro' => 'Ein Plateau bedeutet nicht automatisch, dass dein Plan schlecht ist. Je länger du trainierst, desto langsamer werden Fortschritte. Entscheidend ist, systematisch zu prüfen, wo der Engpass liegt.',
		'sections' => array(
			array( 'ERST PRÜFEN, DANN ÄNDERN', 'Bevor du dein Programm wechselst, kontrolliere Ausführung, Trainingsdaten und Erholung.', array(
				array( 'Technik filmen', 'Eine bessere Bewegung kann Leistung freisetzen, ohne das Programm zu ändern.' ),
				array( 'Volumen prüfen', 'Zu wenig Reiz und zu viel Ermüdung können ähnlich aussehen.' ),
				array( 'Erholung ansehen', 'Schlaf, Energiezufuhr und Stress beeinflussen Leistung deutlich.' ),
			) ),
			array( 'EINE VARIABLE VERÄNDERN', 'Passe zunächst nur eine Sache an: zusätzliche Wiederholungen, einen Satz, eine leichtere Woche oder eine andere Übungsvariante. So erkennst du, was tatsächlich wirkt.', array(), array( 'Deload einplanen', 'Eine geplante leichtere Woche kann Ermüdung senken und die Qualität der folgenden Trainingsphase verbessern.' ) ),
		),
	),
	array(
		'title' => 'Fitnessroutine trotz vollem Kalender', 'slug' => 'fitnessroutine-trotz-vollem-kalender', 'image' => 'membership',
		'lead' => 'Wie du mit kurzen Einheiten und festen Auslösern auch in stressigen Wochen dranbleibst.',
		'meta' => 'Fitness trotz wenig Zeit: kurze Trainingspläne, feste Routinen und praktische Strategien für einen vollen Alltag.',
		'intro' => 'Zeitmangel lässt sich nicht immer lösen. Aber Training kann so geplant werden, dass es weniger Zeit beansprucht und weniger Entscheidungen erfordert.',
		'sections' => array(
			array( 'MINDESTSTANDARD DEFINIEREN', 'Lege fest, welche kleine Einheit auch in einer vollen Woche möglich ist. Zum Beispiel zweimal 30 Minuten Ganzkörpertraining.', array(
				array( 'Feste Termine', 'Behandle Training wie einen Termin und blockiere die Zeit im Kalender.' ),
				array( 'Kurze Wege', 'Packe deine Tasche am Vorabend und wähle einen gut erreichbaren Club.' ),
				array( 'Plan B', 'Halte eine 20-Minuten-Einheit für besonders volle Tage bereit.' ),
			) ),
			array( 'NICHT NACHHOLEN, WEITERMACHEN', 'Eine ausgefallene Einheit muss nicht kompensiert werden. Kehre einfach beim nächsten geplanten Termin in deine Routine zurück.', array(), array( 'Prioritäten', 'Konzentriere dich auf wenige große Bewegungen und vermeide unnötige Wartezeiten. Gute Planung macht kurze Einheiten wirksam.' ) ),
		),
	),
	array(
		'title' => 'Regeneration nach intensivem Training', 'slug' => 'regeneration-nach-intensivem-training', 'image' => 'recovery',
		'lead' => 'Was Muskelkater bedeutet und wie du zwischen Trainingstagen sinnvoll regenerierst.',
		'meta' => 'Regeneration nach dem Training: Muskelkater einordnen, Schlaf, Ernährung und aktive Erholung sinnvoll nutzen.',
		'intro' => 'Muskelkater ist eine normale Reaktion auf ungewohnte Belastung, aber kein zuverlässiger Maßstab für ein gutes Training. Ziel ist ein Reiz, von dem du dich rechtzeitig erholen kannst.',
		'sections' => array(
			array( 'DIE GRUNDLAGEN', 'Komplizierte Recovery-Tools ersetzen keine Basisgewohnheiten.', array(
				array( 'Schlaf', 'Regelmäßige und ausreichende Schlafdauer bleibt der wichtigste Faktor.' ),
				array( 'Ernährung', 'Genug Energie, Protein und Flüssigkeit unterstützen Anpassung.' ),
				array( 'Bewegung', 'Lockere Aktivität kann sich besser anfühlen als vollständige Inaktivität.' ),
			) ),
			array( 'WANN PAUSIEREN?', 'Starke Erschöpfung, ungewöhnlicher Leistungsabfall oder stechender Schmerz sind Gründe, Belastung zu reduzieren und Beschwerden fachlich abklären zu lassen.', array(), array( 'Training planen', 'Verteile harte Einheiten so, dass dieselben Muskelgruppen ausreichend Zeit zur Erholung haben.' ) ),
		),
	),
);
foreach ( $articles as $article ) {
	$article['eyebrow']  = 'UPTOWN MAGAZIN';
	$article['cta_title'] = 'DEIN WISSEN. DEIN TRAINING.';
	uptown_upsert( 'post', $article );
}

// Site settings.
update_option( 'blogname', 'Uptown Fitness' );
update_option( 'blogdescription', 'Dein smartes 24/7 Gym. Klar. Stark. Für dich.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids['startseite'] );
update_option( 'page_for_posts', $page_ids['magazin'] );
update_option( 'permalink_structure', '/%postname%/' );

// Build a concise primary navigation.
$menu_name = 'Uptown Hauptnavigation';
$menu_obj  = wp_get_nav_menu_object( $menu_name );
$menu_id   = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu( $menu_name );
if ( ! is_wp_error( $menu_id ) ) {
	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $existing_items ) ) {
		$items = array(
			array( 'title' => 'Training', 'url' => home_url( '/training/' ) ),
			array( 'title' => 'Mitgliedschaft', 'id' => $page_ids['mitgliedschaft'] ),
			array( 'title' => 'Standorte', 'id' => $page_ids['standorte'] ),
			array( 'title' => 'Über uns', 'id' => $page_ids['ueber-uns'] ),
			array( 'title' => 'Magazin', 'id' => $page_ids['magazin'] ),
		);
		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'  => $item['title'],
				'menu-item-status' => 'publish',
			);
			if ( ! empty( $item['id'] ) ) {
				$args += array(
					'menu-item-object-id' => $item['id'],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
				);
			} else {
				$args += array(
					'menu-item-url'  => $item['url'],
					'menu-item-type' => 'custom',
				);
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
	}
	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

flush_rewrite_rules();
WP_CLI::success( 'Uptown Fitness content imported: ' . count( $pages ) . ' pages, ' . count( $services ) . ' training pages, ' . count( $trainers ) . ' coaches, ' . count( $articles ) . ' magazine articles.' );
