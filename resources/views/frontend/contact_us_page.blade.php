@php
    $layout = 'frontend.layouts.app';

    if (get_setting('portfolio_landing')) {
        $user = auth()->user();

        if (
            !$user ||
            $user->verification_status == 0 ||
            optional($user->shop)->verification_status == 0
        ) {
            $layout = 'frontend.layouts.portfolio_app';
        }
    }
@endphp

@extends($layout)

@section('meta_title')
    Contact EAI | ELAOUAD Architecture & Ingénierie Casablanca
@endsection

@section('meta_description')
    Contactez ELAOUAD Architecture & Ingénierie à Casablanca pour vos projets d'architecture, ingénierie, BIM, design intérieur, maîtrise d'œuvre, formations ou événements professionnels.
@endsection

@section('meta_keywords')
    contact architecte Casablanca, ELAOUAD Architecture contact, cabinet architecture Casablanca contact, ingénierie construction Casablanca, BIM consulting Maroc contact, design intérieur Casablanca, formation architecture Maroc contact, événements architecture Maroc, EAI Casablanca
@endsection

@section('meta')
    <meta itemprop="name" content="Contact EAI | ELAOUAD Architecture & Ingénierie Casablanca">
    <meta itemprop="description" content="Contactez ELAOUAD Architecture & Ingénierie à Casablanca pour vos projets d'architecture, ingénierie, BIM, design intérieur, maîtrise d'œuvre, formations ou événements professionnels.">
    <meta itemprop="image" content="{{ asset('assets/img/logo.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Contact EAI | ELAOUAD Architecture & Ingénierie Casablanca">
    <meta name="twitter:description" content="Contactez ELAOUAD Architecture & Ingénierie à Casablanca pour vos projets d'architecture, ingénierie, BIM, design intérieur, maîtrise d'œuvre, formations ou événements professionnels.">
    <meta name="twitter:image" content="{{ asset('assets/img/logo.png') }}">

    <meta property="og:title" content="Contact EAI | ELAOUAD Architecture & Ingénierie Casablanca" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/contact') }}" />
    <meta property="og:image" content="{{ asset('assets/img/logo.png') }}" />
    <meta property="og:description" content="Contactez ELAOUAD Architecture & Ingénierie à Casablanca pour vos projets d'architecture, ingénierie, BIM, design intérieur, maîtrise d'œuvre, formations ou événements professionnels." />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
    <meta property="og:locale" content="fr_MA" />

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "name": "Contact ELAOUAD Architecture & Ingénierie",
      "description": "Page de contact pour ELAOUAD Architecture & Ingénierie — Architecture, Ingénierie, BIM, Design Intérieur, Maîtrise d'œuvre, Formations et Événements.",
      "url": "{{ url('/contact') }}",
      "mainEntity": {
        "@type": "ProfessionalService",
        "name": "ELAOUAD Architecture & Ingénierie",
        "image": "{{ asset('assets/img/logo.png') }}",
        "telephone": "+212520198738",
        "email": "contact@eai-construction.com",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "10 Florida Center Park 2, Boulevard Zoulikha Nasri, Sidi Maârouf",
          "addressLocality": "Casablanca",
          "addressCountry": "MA"
        },
        "openingHours": "Mo-Fr 09:00-17:00",
        "url": "{{ url('/') }}",
        "areaServed": {
          "@type": "City",
          "name": "Casablanca"
        }
      }
    }
    </script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ static_asset('assets/css/eai-contact.css') }}">
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     SECTION 1 — HERO
═══════════════════════════════════════════════ --}}
<section class="eai-hero eai-grid-bg">
    <div class="eai-hero-image">
        <img src="{{ static_asset('assets/img/contact/hero-contact.webp') }}" alt="Bureau ELAOUAD Architecture et Ingénierie à Casablanca — Plans architecturaux et réunion de projet" loading="eager" onerror="this.onerror=null; this.src='{{ static_asset('assets/img/contact/hero-contact.svg') }}';">
    </div>
    <div class="eai-hero-overlay"></div>
    <div class="container">
        <div class="eai-hero-content" data-aos="eai-reveal">
            <div class="eai-hero-eyebrow">CONTACT · CONSULTATION · COLLABORATION</div>
            <h1>Chaque projet commence par une <span class="eai-brass-text">conversation claire</span>.</h1>
            <p class="eai-hero-body">
                Vous avez une idée, un terrain, un espace à transformer, un projet à structurer ou une collaboration à proposer ? ELAOUAD Architecture & Ingénierie vous accompagne dès les premières décisions pour clarifier les possibilités, anticiper les contraintes et construire une démarche adaptée.
            </p>
            <p class="eai-hero-secondary">
                Architecture, ingénierie, BIM, design intérieur, maîtrise d'œuvre, formations ou événements professionnels : choisissez le bon sujet, partagez votre besoin et notre équipe vous orientera vers la réponse la plus adaptée.
            </p>
            <ul class="eai-hero-micro-list">
                <li>Projet architectural</li>
                <li>Études techniques</li>
                <li>Design intérieur</li>
                <li>Formation</li>
                <li>Événement</li>
                <li>Partenariat</li>
            </ul>
            <div class="eai-hero-ctas">
                <a href="#contact-form" class="eai-btn-primary">Envoyer une demande</a>
                <a href="/expertises" class="eai-btn-secondary">Voir nos expertises</a>
            </div>
        </div>
    </div>
    <div class="eai-hero-coord">
        <span>CASABLANCA</span>
        33.5229° N, 7.6731° W
    </div>
    <div class="eai-scroll-indicator">Découvrir</div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 2 — CONTACT OPTIONS
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-contact-options">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">NOUS CONTACTER</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Choisissez le canal le plus adapté à votre demande.</h2>
        <p class="eai-intro" data-aos="eai-reveal">
            Selon la nature de votre besoin, vous pouvez nous contacter directement ou remplir le formulaire détaillé afin de nous transmettre les informations essentielles sur votre projet.
        </p>
        <div class="eai-options-grid">

            {{-- 01 — Demande de projet --}}
            <div class="eai-option-card" data-aos="eai-reveal" data-aos-delay="0">
                <div class="eai-option-number">01</div>
                <div class="eai-option-for">Architecture · Ingénierie · BIM · Intérieur</div>
                <div class="eai-option-title">Demande de projet</div>
                <p class="eai-option-text">
                    Présentez votre projet, son contexte, sa localisation, son niveau d'avancement et les objectifs que vous souhaitez atteindre.
                </p>
                <a href="#contact-form" class="eai-option-link">Remplir le formulaire</a>
            </div>

            {{-- 02 — Appel direct --}}
            <div class="eai-option-card" data-aos="eai-reveal" data-aos-delay="100">
                <div class="eai-option-number">02</div>
                <div class="eai-option-for">Prise de contact rapide</div>
                <div class="eai-option-title">Appel direct</div>
                <p class="eai-option-text">
                    Pour une prise de contact rapide ou une demande urgente, contactez notre équipe par téléphone.
                </p>
                <div class="eai-option-detail">+212 520 19 87 38</div>
                <a href="tel:+212520198738" class="eai-option-link">Appeler maintenant</a>
            </div>

            {{-- 03 — WhatsApp --}}
            <div class="eai-option-card" data-aos="eai-reveal" data-aos-delay="200">
                <div class="eai-option-number">03</div>
                <div class="eai-option-for">Orientation rapide</div>
                <div class="eai-option-title">WhatsApp</div>
                <p class="eai-option-text">
                    Pour partager une première information, une localisation, des images ou demander une orientation rapide, contactez-nous par WhatsApp.
                </p>
                <div class="eai-option-detail">+212 666 79 85 36</div>
                <a href="https://wa.me/212666798536" target="_blank" rel="noopener noreferrer" class="eai-option-link">Écrire sur WhatsApp</a>
            </div>

            {{-- 04 — Email professionnel --}}
            <div class="eai-option-card" data-aos="eai-reveal" data-aos-delay="300">
                <div class="eai-option-number">04</div>
                <div class="eai-option-for">Dossier · Institution · Partenariat</div>
                <div class="eai-option-title">Email professionnel</div>
                <p class="eai-option-text">
                    Pour envoyer un dossier, une demande institutionnelle, une proposition de partenariat ou une demande détaillée.
                </p>
                <div class="eai-option-detail">contact@eai-construction.com</div>
                <a href="mailto:contact@eai-construction.com" class="eai-option-link">Envoyer un email</a>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 3 — MAIN CONTACT FORM
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-form-section" id="contact-form">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">FORMULAIRE DE CONTACT</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Présentez votre demande avec <span class="eai-brass-text">précision</span>.</h2>
        <p class="eai-intro" data-aos="eai-reveal">
            Plus votre demande est claire, plus notre équipe pourra vous orienter efficacement. Vous pouvez renseigner uniquement les informations disponibles aujourd'hui ; les détails seront clarifiés lors du premier échange.
        </p>

        <div class="eai-form-wrapper" data-aos="eai-reveal">
            <form class="eai-form-grid" id="eai-contact-form" role="form" action="{{ route('contact.store') }}" method="POST">
                @csrf

                {{-- 1. Nom complet --}}
                <div class="eai-form-group" id="group-fullName">
                    <label for="fullName" class="eai-form-label">Nom complet <span class="eai-required">*</span></label>
                    <input type="text" id="fullName" name="name" class="eai-form-input" placeholder="Votre nom et prénom" value="{{ old('name') }}" required>
                    <span class="eai-form-error">Veuillez entrer votre nom complet.</span>
                </div>

                {{-- 2. Email --}}
                <div class="eai-form-group" id="group-email">
                    <label for="email" class="eai-form-label">Email <span class="eai-required">*</span></label>
                    <input type="email" id="email" name="email" class="eai-form-input" placeholder="votre@email.com" value="{{ old('email') }}" required>
                    <span class="eai-form-error">Veuillez entrer un email valide.</span>
                </div>

                {{-- 3. Téléphone / WhatsApp --}}
                <div class="eai-form-group" id="group-phone">
                    <label for="phone" class="eai-form-label">Téléphone / WhatsApp <span class="eai-required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="eai-form-input" placeholder="+212 ..." value="{{ old('phone') }}" required>
                    <span class="eai-form-error">Veuillez entrer votre numéro de téléphone.</span>
                </div>

                {{-- 4. Ville / Pays --}}
                <div class="eai-form-group" id="group-city">
                    <label for="city" class="eai-form-label">Ville / Pays</label>
                    <input type="text" id="city" name="city" class="eai-form-input" placeholder="Casablanca, Maroc" value="{{ old('city') }}">
                </div>

                {{-- 5. Profil --}}
                <div class="eai-form-group" id="group-profile">
                    <label for="profile" class="eai-form-label">Profil <span class="eai-required">*</span></label>
                    <select id="profile" name="profile" class="eai-form-select" required>
                        <option value="" disabled selected>Sélectionnez votre profil</option>
                        <option value="Particulier" {{ old('profile') == 'Particulier' ? 'selected' : '' }}>Particulier</option>
                        <option value="Promoteur immobilier" {{ old('profile') == 'Promoteur immobilier' ? 'selected' : '' }}>Promoteur immobilier</option>
                        <option value="Entreprise" {{ old('profile') == 'Entreprise' ? 'selected' : '' }}>Entreprise</option>
                        <option value="Institution" {{ old('profile') == 'Institution' ? 'selected' : '' }}>Institution</option>
                        <option value="Architecte / Designer" {{ old('profile') == 'Architecte / Designer' ? 'selected' : '' }}>Architecte / Designer</option>
                        <option value="Ingénieur / Bureau d'études" {{ old('profile') == "Ingénieur / Bureau d'études" ? 'selected' : '' }}>Ingénieur / Bureau d'études</option>
                        <option value="Étudiant / Participant formation" {{ old('profile') == 'Étudiant / Participant formation' ? 'selected' : '' }}>Étudiant / Participant formation</option>
                        <option value="Exposant / Partenaire événement" {{ old('profile') == 'Exposant / Partenaire événement' ? 'selected' : '' }}>Exposant / Partenaire événement</option>
                        <option value="Autre" {{ old('profile') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    <span class="eai-form-error">Veuillez sélectionner votre profil.</span>
                </div>

                {{-- 6. Type de demande --}}
                <div class="eai-form-group" id="group-requestType">
                    <label for="requestType" class="eai-form-label">Type de demande <span class="eai-required">*</span></label>
                    <select id="requestType" name="request_type" class="eai-form-select" required>
                        <option value="" disabled selected>Sélectionnez le type de demande</option>
                        <option value="Projet architectural" {{ old('request_type') == 'Projet architectural' ? 'selected' : '' }}>Projet architectural</option>
                        <option value="Architecture d'intérieur" {{ old('request_type') == "Architecture d'intérieur" ? 'selected' : '' }}>Architecture d'intérieur</option>
                        <option value="Ingénierie / études techniques" {{ old('request_type') == 'Ingénierie / études techniques' ? 'selected' : '' }}>Ingénierie / études techniques</option>
                        <option value="BIM Consulting" {{ old('request_type') == 'BIM Consulting' ? 'selected' : '' }}>BIM Consulting</option>
                        <option value="Maîtrise d'œuvre / suivi travaux" {{ old('request_type') == 'Maîtrise d\'œuvre / suivi travaux' ? 'selected' : '' }}>Maîtrise d'œuvre / suivi travaux</option>
                        <option value="Urbanisme / aménagement" {{ old('request_type') == 'Urbanisme / aménagement' ? 'selected' : '' }}>Urbanisme / aménagement</option>
                        <option value="Formation EAI Courses" {{ old('request_type') == 'Formation EAI Courses' ? 'selected' : '' }}>Formation EAI Courses</option>
                        <option value="Événement / partenariat ELAOUAD Events" {{ old('request_type') == 'Événement / partenariat ELAOUAD Events' ? 'selected' : '' }}>Événement / partenariat ELAOUAD Events</option>
                        <option value="Demande de collaboration" {{ old('request_type') == 'Demande de collaboration' ? 'selected' : '' }}>Demande de collaboration</option>
                        <option value="Autre" {{ old('request_type') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    <span class="eai-form-error">Veuillez sélectionner le type de demande.</span>
                </div>

                {{-- 7. Niveau d'avancement --}}
                <div class="eai-form-group" id="group-projectStage">
                    <label for="projectStage" class="eai-form-label">Niveau d'avancement</label>
                    <select id="projectStage" name="project_stage" class="eai-form-select">
                        <option value="" disabled selected>Sélectionnez le niveau</option>
                        <option value="Idée initiale" {{ old('project_stage') == 'Idée initiale' ? 'selected' : '' }}>Idée initiale</option>
                        <option value="Terrain disponible" {{ old('project_stage') == 'Terrain disponible' ? 'selected' : '' }}>Terrain disponible</option>
                        <option value="Plans existants" {{ old('project_stage') == 'Plans existants' ? 'selected' : '' }}>Plans existants</option>
                        <option value="Projet en étude" {{ old('project_stage') == 'Projet en étude' ? 'selected' : '' }}>Projet en étude</option>
                        <option value="Projet en chantier" {{ old('project_stage') == 'Projet en chantier' ? 'selected' : '' }}>Projet en chantier</option>
                        <option value="Projet à reprendre / optimiser" {{ old('project_stage') == 'Projet à reprendre / optimiser' ? 'selected' : '' }}>Projet à reprendre / optimiser</option>
                        <option value="Demande professionnelle spécifique" {{ old('project_stage') == 'Demande professionnelle spécifique' ? 'selected' : '' }}>Demande professionnelle spécifique</option>
                    </select>
                </div>

                {{-- 8. Budget estimatif --}}
                <div class="eai-form-group" id="group-budget">
                    <label for="budget" class="eai-form-label">Budget estimatif</label>
                    <select id="budget" name="budget" class="eai-form-select">
                        <option value="" disabled selected>Sélectionnez une fourchette</option>
                        <option value="À définir" {{ old('budget') == 'À définir' ? 'selected' : '' }}>À définir</option>
                        <option value="Moins de 100 000 MAD" {{ old('budget') == 'Moins de 100 000 MAD' ? 'selected' : '' }}>Moins de 100 000 MAD</option>
                        <option value="100 000 — 500 000 MAD" {{ old('budget') == '100 000 — 500 000 MAD' ? 'selected' : '' }}>100 000 — 500 000 MAD</option>
                        <option value="500 000 — 1 000 000 MAD" {{ old('budget') == '500 000 — 1 000 000 MAD' ? 'selected' : '' }}>500 000 — 1 000 000 MAD</option>
                        <option value="1 000 000 — 3 000 000 MAD" {{ old('budget') == '1 000 000 — 3 000 000 MAD' ? 'selected' : '' }}>1 000 000 — 3 000 000 MAD</option>
                        <option value="Plus de 3 000 000 MAD" {{ old('budget') == 'Plus de 3 000 000 MAD' ? 'selected' : '' }}>Plus de 3 000 000 MAD</option>
                        <option value="Non applicable" {{ old('budget') == 'Non applicable' ? 'selected' : '' }}>Non applicable</option>
                    </select>
                </div>

                {{-- 9. Délai souhaité --}}
                <div class="eai-form-group" id="group-timeline">
                    <label for="timeline" class="eai-form-label">Délai souhaité</label>
                    <select id="timeline" name="timeline" class="eai-form-select">
                        <option value="" disabled selected>Sélectionnez un délai</option>
                        <option value="Urgent" {{ old('timeline') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Dans le mois" {{ old('timeline') == 'Dans le mois' ? 'selected' : '' }}>Dans le mois</option>
                        <option value="1 à 3 mois" {{ old('timeline') == '1 à 3 mois' ? 'selected' : '' }}>1 à 3 mois</option>
                        <option value="3 à 6 mois" {{ old('timeline') == '3 à 6 mois' ? 'selected' : '' }}>3 à 6 mois</option>
                        <option value="Plus de 6 mois" {{ old('timeline') == 'Plus de 6 mois' ? 'selected' : '' }}>Plus de 6 mois</option>
                        <option value="À définir" {{ old('timeline') == 'À définir' ? 'selected' : '' }}>À définir</option>
                    </select>
                </div>

                {{-- 10. Message --}}
                <div class="eai-form-group eai-full-width" id="group-message">
                    <label for="message" class="eai-form-label">Message <span class="eai-required">*</span></label>
                    <textarea id="message" name="content" class="eai-form-textarea" placeholder="Décrivez votre projet, votre besoin, votre objectif, la localisation, les documents disponibles et toute information utile." rows="5" required>{{ old('content') }}</textarea>
                    <span class="eai-form-error">Veuillez décrire votre demande.</span>
                </div>

                {{-- Hidden fields for extended data --}}
                <input type="hidden" name="eai_city" id="eai_city" value="">
                <input type="hidden" name="eai_profile" id="eai_profile" value="">
                <input type="hidden" name="eai_request_type" id="eai_request_type" value="">
                <input type="hidden" name="eai_project_stage" id="eai_project_stage" value="">
                <input type="hidden" name="eai_budget" id="eai_budget" value="">
                <input type="hidden" name="eai_timeline" id="eai_timeline" value="">

                {{-- 11. Consentement --}}
                <div class="eai-form-checkbox-group" id="group-consent">
                    <input type="checkbox" id="consent" name="consent" class="eai-form-checkbox" required>
                    <label for="consent" class="eai-form-checkbox-label">
                        J'accepte d'être contacté(e) par ELAOUAD Architecture & Ingénierie au sujet de ma demande. <span class="eai-required">*</span>
                    </label>
                </div>
                <span class="eai-form-error" id="consent-error" style="grid-column: 1 / -1; margin-top: -12px;">Vous devez accepter d'être contacté.</span>

                {{-- Recaptcha --}}
                @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_contact_form') == 1)
                    @if ($errors->has('g-recaptcha-response'))
                        <span class="eai-form-error" style="grid-column: 1 / -1; display: block;" role="alert">
                            {{ $errors->first('g-recaptcha-response') }}
                        </span>
                    @endif
                @endif

                {{-- Cloudflare Turnstile --}}
                @if(get_setting('cloudflare_turnstile') == 1 && get_setting('turnstile_contact_form') == 1)
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" style="grid-column: 1 / -1;"></div>
                    <input type="hidden" name="turnstile_action" value="turnstile_contact_form">
                    @if ($errors->has('cf-turnstile-response'))
                        <span class="eai-form-error" style="grid-column: 1 / -1; display: block;" role="alert">
                            {{ $errors->first('cf-turnstile-response') }}
                        </span>
                    @endif
                @endif

                {{-- Submit --}}
                <div class="eai-form-submit-wrap">
                    @if (env('MAIL_USERNAME') == null && env('MAIL_PASSWORD') == null)
                        <a class="eai-form-submit" href="javascript:void(0)" onclick="showEaiWarning()">
                            Envoyer ma demande
                        </a>
                    @else
                        <button type="submit" class="eai-form-submit" id="eai-submit-btn">
                            Envoyer ma demande
                        </button>
                    @endif
                    <span class="eai-form-note">Votre demande sera traitée par notre équipe dans les meilleurs délais.</span>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 4 — PREPARE YOUR REQUEST
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-prepare-section">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">PRÉPARER VOTRE DEMANDE</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Les informations qui nous aident à mieux comprendre votre projet.</h2>
        <p class="eai-intro" data-aos="eai-reveal">
            Vous n'avez pas besoin d'avoir un dossier complet pour nous contacter. Cependant, certains éléments peuvent accélérer l'analyse et rendre le premier échange plus efficace.
        </p>
        <div class="eai-prepare-grid">

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="0">
                <div class="eai-prepare-number">01</div>
                <div class="eai-prepare-title">Localisation</div>
                <p class="eai-prepare-text">Adresse, ville, quartier, terrain, bâtiment existant ou zone d'intervention.</p>
            </div>

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="100">
                <div class="eai-prepare-number">02</div>
                <div class="eai-prepare-title">Type de projet</div>
                <p class="eai-prepare-text">Villa, appartement, bureau, commerce, hôtel, espace public, formation, événement, étude technique ou autre.</p>
            </div>

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="200">
                <div class="eai-prepare-number">03</div>
                <div class="eai-prepare-title">Objectif principal</div>
                <p class="eai-prepare-text">Construire, rénover, aménager, étudier, coordonner, former, exposer, participer ou développer une collaboration.</p>
            </div>

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="0">
                <div class="eai-prepare-number">04</div>
                <div class="eai-prepare-title">Documents disponibles</div>
                <p class="eai-prepare-text">Plans, photos, relevés, certificat de propriété, programme, cahier des charges, images d'inspiration ou études existantes.</p>
            </div>

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="100">
                <div class="eai-prepare-number">05</div>
                <div class="eai-prepare-title">Budget indicatif</div>
                <p class="eai-prepare-text">Une enveloppe même approximative permet d'orienter les priorités et d'éviter les propositions irréalistes.</p>
            </div>

            <div class="eai-prepare-card" data-aos="eai-reveal" data-aos-delay="200">
                <div class="eai-prepare-number">06</div>
                <div class="eai-prepare-title">Délai souhaité</div>
                <p class="eai-prepare-text">Date de démarrage, urgence, calendrier prévisionnel, échéance administrative ou objectif de livraison.</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 5 — REQUEST ROUTING
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-routing-section">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">ORIENTATION</div>
        <h2 class="eai-headline" data-aos="eai-reveal">À qui s'adresse votre demande ?</h2>
        <div class="eai-routing-panels" data-aos="eai-reveal">

            {{-- 01 — Projet architectural --}}
            <div class="eai-routing-panel active">
                <div class="eai-routing-header" onclick="toggleEaiRouting(this)">
                    <div class="eai-routing-header-left">
                        <span class="eai-routing-number">01</span>
                        <span class="eai-routing-title">Vous avez un projet architectural</span>
                    </div>
                    <span class="eai-routing-toggle">+</span>
                </div>
                <div class="eai-routing-body">
                    <div class="eai-routing-body-inner">
                        <p class="eai-routing-text">
                            Nous pouvons vous accompagner dans la conception, l'étude, les plans, les démarches, la coordination et l'orientation générale du projet.
                        </p>
                        <a href="#contact-form" class="eai-routing-cta">Sélectionner "Projet architectural" dans le formulaire</a>
                    </div>
                </div>
            </div>

            {{-- 02 — Transformer un intérieur --}}
            <div class="eai-routing-panel">
                <div class="eai-routing-header" onclick="toggleEaiRouting(this)">
                    <div class="eai-routing-header-left">
                        <span class="eai-routing-number">02</span>
                        <span class="eai-routing-title">Vous souhaitez transformer un intérieur</span>
                    </div>
                    <span class="eai-routing-toggle">+</span>
                </div>
                <div class="eai-routing-body">
                    <div class="eai-routing-body-inner">
                        <p class="eai-routing-text">
                            Nous pouvons vous aider à repenser les volumes, les circulations, les matériaux, la lumière, le mobilier, les ambiances et l'expérience d'usage.
                        </p>
                        <a href="#contact-form" class="eai-routing-cta">Sélectionner "Architecture d'intérieur"</a>
                    </div>
                </div>
            </div>

            {{-- 03 — Formation --}}
            <div class="eai-routing-panel">
                <div class="eai-routing-header" onclick="toggleEaiRouting(this)">
                    <div class="eai-routing-header-left">
                        <span class="eai-routing-number">03</span>
                        <span class="eai-routing-title">Vous cherchez une formation</span>
                    </div>
                    <span class="eai-routing-toggle">+</span>
                </div>
                <div class="eai-routing-body">
                    <div class="eai-routing-body-inner">
                        <p class="eai-routing-text">
                            Pour les formations en BIM, Revit, AutoCAD, SketchUp, design intérieur, gestion de projet ou conduite de chantier, vous pouvez accéder à la plateforme EAI Courses ou demander une orientation.
                        </p>
                        <a href="https://courses.eai-construction.com" target="_blank" rel="noopener noreferrer" class="eai-routing-cta">Accéder à EAI Courses</a>
                    </div>
                </div>
            </div>

            {{-- 04 — Événement --}}
            <div class="eai-routing-panel">
                <div class="eai-routing-header" onclick="toggleEaiRouting(this)">
                    <div class="eai-routing-header-left">
                        <span class="eai-routing-number">04</span>
                        <span class="eai-routing-title">Vous voulez participer à un événement</span>
                    </div>
                    <span class="eai-routing-toggle">+</span>
                </div>
                <div class="eai-routing-body">
                    <div class="eai-routing-body-inner">
                        <p class="eai-routing-text">
                            Pour les forums, conférences, expositions, workshops, stands, sponsoring ou partenariats, vous pouvez accéder à la plateforme ELAOUAD Events ou nous contacter directement.
                        </p>
                        <a href="https://events.eai-construction.com" target="_blank" rel="noopener noreferrer" class="eai-routing-cta">Accéder à EAI Events</a>
                    </div>
                </div>
            </div>

            {{-- 05 — Entreprise / Partenaire --}}
            <div class="eai-routing-panel">
                <div class="eai-routing-header" onclick="toggleEaiRouting(this)">
                    <div class="eai-routing-header-left">
                        <span class="eai-routing-number">05</span>
                        <span class="eai-routing-title">Vous êtes une entreprise, marque ou partenaire</span>
                    </div>
                    <span class="eai-routing-toggle">+</span>
                </div>
                <div class="eai-routing-body">
                    <div class="eai-routing-body-inner">
                        <p class="eai-routing-text">
                            Pour une collaboration professionnelle, une demande B2B, une proposition de partenariat ou une intervention, partagez votre objectif et vos coordonnées.
                        </p>
                        <a href="#contact-form" class="eai-routing-cta">Remplir le formulaire</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 6 — OFFICE / CONTACT DETAILS
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-office-section">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">COORDONNÉES</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Retrouvez EAI à <span class="eai-brass-text">Casablanca</span>.</h2>
        <div class="eai-office-grid">

            <div class="eai-office-card" data-aos="eai-reveal">
                <div class="eai-office-company">ELAOUAD Architecture & Ingénierie</div>

                <div class="eai-office-item">
                    <div class="eai-office-item-label">Adresse</div>
                    <div class="eai-office-item-value">
                        10 Florida Center Park 2,<br>
                        Boulevard Zoulikha Nasri,<br>
                        Sidi Maârouf,<br>
                        Casablanca, Maroc
                    </div>
                </div>

                <div class="eai-office-item">
                    <div class="eai-office-item-label">Email</div>
                    <div class="eai-office-item-value">
                        <a href="mailto:contact@eai-construction.com">contact@eai-construction.com</a>
                    </div>
                </div>

                <div class="eai-office-item">
                    <div class="eai-office-item-label">Téléphone</div>
                    <div class="eai-office-item-value">
                        <a href="tel:+212520198738">+212 520 19 87 38</a>
                    </div>
                </div>

                <div class="eai-office-item">
                    <div class="eai-office-item-label">WhatsApp</div>
                    <div class="eai-office-item-value">
                        <a href="https://wa.me/212666798536" target="_blank" rel="noopener noreferrer">+212 666 79 85 36</a>
                    </div>
                </div>

                <div class="eai-office-item">
                    <div class="eai-office-item-label">Horaires</div>
                    <div class="eai-office-item-value">Lundi à vendredi · 9h00 — 17h00</div>
                </div>

                <div class="eai-office-actions">
                    <a href="https://www.google.com/maps/search/10+Florida+Center+Park+2+Boulevard+Zoulikha+Nasri+Sidi+Maarouf+Casablanca+Maroc" target="_blank" rel="noopener noreferrer" class="eai-office-btn eai-office-btn-primary">Ouvrir Google Maps</a>
                    <a href="tel:+212520198738" class="eai-office-btn eai-office-btn-outline">Appeler</a>
                    <a href="mailto:contact@eai-construction.com" class="eai-office-btn eai-office-btn-outline">Envoyer un email</a>
                </div>
            </div>

            <div class="eai-map-container" data-aos="eai-reveal" data-aos-delay="200">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3324.5!2d-7.6731!3d33.5229!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzPCsDMxJzIyLjQiTiA3wrA0MCcyMy4yIlc!5e0!3m2!1sfr!2sma!4v1700000000000!5m2!1sfr!2sma"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Localisation ELAOUAD Architecture et Ingénierie — Sidi Maârouf, Casablanca">
                </iframe>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 7 — AFTER SUBMISSION
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-after-section">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">APRÈS L'ENVOI</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Une prise de contact structurée, sans improvisation.</h2>
        <div class="eai-timeline" data-aos="eai-reveal">

            <div class="eai-timeline-step">
                <div class="eai-timeline-dot">01</div>
                <div class="eai-timeline-title">Réception</div>
                <p class="eai-timeline-text">Votre demande est reçue avec les informations transmises via le formulaire.</p>
            </div>

            <div class="eai-timeline-step">
                <div class="eai-timeline-dot">02</div>
                <div class="eai-timeline-title">Qualification</div>
                <p class="eai-timeline-text">L'équipe identifie le type de besoin, le niveau d'urgence, les informations manquantes et le bon interlocuteur.</p>
            </div>

            <div class="eai-timeline-step">
                <div class="eai-timeline-dot">03</div>
                <div class="eai-timeline-title">Premier échange</div>
                <p class="eai-timeline-text">Nous vous contactons pour clarifier le contexte, les objectifs, les contraintes, les documents disponibles et les prochaines étapes possibles.</p>
            </div>

            <div class="eai-timeline-step">
                <div class="eai-timeline-dot">04</div>
                <div class="eai-timeline-title">Orientation</div>
                <p class="eai-timeline-text">Selon la demande, nous pouvons proposer une consultation, une mission, une orientation vers EAI Courses, ELAOUAD Events ou une demande de documents complémentaires.</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 8 — FAQ
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-faq-section">
    <div class="container">
        <div class="eai-section-label" data-aos="eai-reveal">QUESTIONS FRÉQUENTES</div>
        <h2 class="eai-headline" data-aos="eai-reveal">Avant de nous écrire.</h2>
        <div class="eai-faq-list" data-aos="eai-reveal">

            <div class="eai-faq-item active">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">Puis-je contacter EAI même si mon projet est encore au stade d'idée ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        Oui. Une idée initiale peut déjà être discutée. L'objectif du premier échange est justement de clarifier le contexte, les possibilités, les contraintes et les prochaines étapes.
                    </div>
                </div>
            </div>

            <div class="eai-faq-item">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">Quels documents dois-je envoyer ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        Vous pouvez envoyer les documents disponibles : plans, photos, localisation, programme, images d'inspiration, dossier existant ou cahier des charges. Si vous n'avez aucun document, décrivez simplement votre besoin.
                    </div>
                </div>
            </div>

            <div class="eai-faq-item">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">EAI intervient-elle uniquement à Casablanca ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        EAI est basée à Casablanca, mais les demandes peuvent concerner d'autres villes selon la nature du projet, la mission et les conditions d'intervention.
                    </div>
                </div>
            </div>

            <div class="eai-faq-item">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">Puis-je demander une formation via cette page ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        Oui. Vous pouvez sélectionner "Formation EAI Courses" dans le formulaire ou accéder directement à la plateforme EAI Courses pour consulter les programmes disponibles.
                    </div>
                </div>
            </div>

            <div class="eai-faq-item">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">Puis-je demander un partenariat ou un stand pour un événement ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        Oui. Les demandes liées aux événements, expositions, conférences, sponsoring ou partenariats peuvent être envoyées via le formulaire ou consultées sur la plateforme ELAOUAD Events.
                    </div>
                </div>
            </div>

            <div class="eai-faq-item">
                <div class="eai-faq-question" onclick="toggleEaiFaq(this)">
                    <span class="eai-faq-question-text">Le formulaire remplace-t-il une consultation ?</span>
                    <span class="eai-faq-toggle">+</span>
                </div>
                <div class="eai-faq-answer">
                    <div class="eai-faq-answer-inner">
                        Non. Le formulaire permet de transmettre les informations initiales. Une consultation ou une mission peut ensuite être proposée selon la nature de la demande.
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     SECTION 9 — FINAL CTA
═══════════════════════════════════════════════ --}}
<section class="eai-section eai-final-cta eai-grid-bg">
    <div class="container">
        <div class="eai-final-cta-inner" data-aos="eai-reveal">
            <div class="eai-section-label" style="justify-content: center;">INITIER UNE COLLABORATION</div>
            <h2>Votre projet mérite une <span class="eai-brass-text">première décision claire</span>.</h2>
            <p>
                Écrivez-nous avec les informations dont vous disposez aujourd'hui. Nous vous aiderons à clarifier le besoin, identifier les priorités et définir la meilleure manière d'avancer.
            </p>
            <div class="eai-final-cta-actions">
                <a href="#contact-form" class="eai-btn-primary">Remplir le formulaire</a>
                <a href="/projets" class="eai-btn-secondary">Voir nos projets</a>
            </div>
        </div>
    </div>
</section>

@endsection

@section('script')
    @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_contact_form') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
        <script type="text/javascript">
            document.getElementById('eai-contact-form').addEventListener('submit', function(e) {
                e.preventDefault();
                if (!validateEaiForm()) return;
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env('CAPTCHA_KEY') }}', {action: 'contact_us'}).then(function(token) {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'hidden');
                        input.setAttribute('name', 'g-recaptcha-response');
                        input.setAttribute('value', token);
                        e.target.appendChild(input);
                        e.target.submit();
                    });
                });
            });
        </script>
    @endif

    <script type="text/javascript">
        function showEaiWarning() {
            AIZ.plugins.notify('warning', "Une erreur est survenue. Veuillez nous contacter directement par email à contact@eai-construction.com.");
            return false;
        }

        /* ── Routing Accordion ── */
        function toggleEaiRouting(header) {
            var panel = header.parentElement;
            var wasActive = panel.classList.contains('active');
            document.querySelectorAll('.eai-routing-panel').forEach(function(p) {
                p.classList.remove('active');
            });
            if (!wasActive) {
                panel.classList.add('active');
            }
        }

        /* ── FAQ Accordion ── */
        function toggleEaiFaq(question) {
            var item = question.parentElement;
            var wasActive = item.classList.contains('active');
            document.querySelectorAll('.eai-faq-item').forEach(function(i) {
                i.classList.remove('active');
            });
            if (!wasActive) {
                item.classList.add('active');
            }
        }

        /* ── Form Validation ── */
        function validateEaiForm() {
            var valid = true;
            var fields = [
                { id: 'fullName', group: 'group-fullName' },
                { id: 'email', group: 'group-email' },
                { id: 'phone', group: 'group-phone' },
                { id: 'profile', group: 'group-profile' },
                { id: 'requestType', group: 'group-requestType' },
                { id: 'message', group: 'group-message' }
            ];

            fields.forEach(function(f) {
                var el = document.getElementById(f.id);
                var group = document.getElementById(f.group);
                if (!el.value || el.value.trim() === '') {
                    group.classList.add('has-error');
                    valid = false;
                } else {
                    group.classList.remove('has-error');
                }
            });

            /* Email format */
            var emailEl = document.getElementById('email');
            if (emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
                document.getElementById('group-email').classList.add('has-error');
                valid = false;
            }

            /* Consent */
            var consentEl = document.getElementById('consent');
            var consentError = document.getElementById('consent-error');
            if (!consentEl.checked) {
                consentError.style.display = 'block';
                valid = false;
            } else {
                consentError.style.display = 'none';
            }

            return valid;
        }

        /* ── Populate hidden fields on submit ── */
        document.getElementById('eai-contact-form').addEventListener('submit', function(e) {
            if (!validateEaiForm()) {
                e.preventDefault();
                return;
            }

            /* Populate hidden fields with extended data */
            document.getElementById('eai_city').value = document.getElementById('city').value;
            document.getElementById('eai_profile').value = document.getElementById('profile').value;
            document.getElementById('eai_request_type').value = document.getElementById('requestType').value;
            document.getElementById('eai_project_stage').value = document.getElementById('projectStage').value;
            document.getElementById('eai_budget').value = document.getElementById('budget').value;
            document.getElementById('eai_timeline').value = document.getElementById('timeline').value;

            /* Build enriched content for the backend */
            var city = document.getElementById('city').value;
            var profile = document.getElementById('profile').value;
            var requestType = document.getElementById('requestType').value;
            var projectStage = document.getElementById('projectStage').value;
            var budget = document.getElementById('budget').value;
            var timeline = document.getElementById('timeline').value;
            var message = document.getElementById('message').value;

            var enrichedContent = 'Profil: ' + profile + '\n';
            enrichedContent += 'Type de demande: ' + requestType + '\n';
            if (city) enrichedContent += 'Ville/Pays: ' + city + '\n';
            if (projectStage) enrichedContent += 'Niveau d\'avancement: ' + projectStage + '\n';
            if (budget) enrichedContent += 'Budget estimatif: ' + budget + '\n';
            if (timeline) enrichedContent += 'Délai souhaité: ' + timeline + '\n';
            enrichedContent += '\n--- Message ---\n' + message;

            document.getElementById('message').value = enrichedContent;
        });

        /* ── Remove error on input ── */
        document.querySelectorAll('.eai-form-input, .eai-form-select, .eai-form-textarea').forEach(function(el) {
            el.addEventListener('input', function() {
                var group = el.closest('.eai-form-group');
                if (group) group.classList.remove('has-error');
            });
            el.addEventListener('change', function() {
                var group = el.closest('.eai-form-group');
                if (group) group.classList.remove('has-error');
            });
        });

        document.getElementById('consent').addEventListener('change', function() {
            document.getElementById('consent-error').style.display = 'none';
        });

        /* ── Smooth scroll for anchor links ── */
        document.querySelectorAll('a[href^="#contact-form"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector('#contact-form');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        /* ── Init AOS ── */
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 80
            });
        }
    </script>
@endsection
