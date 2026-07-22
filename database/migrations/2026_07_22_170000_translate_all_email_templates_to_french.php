<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $frenchTemplates = [
            'customer_reg_email_to_admin' => [
                'subject' => 'Nouvelle inscription client - [[customer_name]]',
                'default_text' => 'Un nouveau client s\'est inscrit sur [[store_name]].<br><br><strong>Détails du client :</strong><br>Nom : [[customer_name]]<br>Email : [[email]]<br>Date d\'inscription : [[date]]',
            ],
            'registration_email_to_customer' => [
                'subject' => 'Bienvenue sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Bienvenue sur [[store_name]] ! Votre compte client a été créé avec succès.',
            ],
            'registration_from_system_email_to_customer' => [
                'subject' => 'Bienvenue sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre compte a été créé sur [[store_name]]. Votre mot de passe temporaire est : <strong>[[password]]</strong>',
            ],
            'seller_reg_email_to_admin' => [
                'subject' => 'Nouvelle inscription vendeur - [[shop_name]]',
                'default_text' => 'Un nouveau vendeur s\'est inscrit sur [[store_name]].<br><br><strong>Détails de la boutique :</strong><br>Nom de la boutique : [[seller_shop_name]]<br>Nom du vendeur : [[seller_name]]<br>Email : [[seller_email]]<br>Adresse : [[seller_shop_address]]<br>Date : [[date]]',
            ],
            'registration_email_to_seller' => [
                'subject' => 'Bienvenue sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Félicitations pour votre inscription en tant que vendeur sur [[store_name]] ! Votre boutique [[seller_shop_name]] a bien été enregistrée.',
            ],
            'registration_from_system_email_to_seller' => [
                'subject' => 'Bienvenue sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Votre compte vendeur a été créé sur [[store_name]]. Votre mot de passe temporaire est : <strong>[[password]]</strong>',
            ],
            'deliveryboy_reg_email_to_admin' => [
                'subject' => 'Nouvelle inscription livreur - [[store_name]]',
                'default_text' => 'Un nouveau livreur s\'est inscrit sur [[store_name]].<br><br>Nom : [[delivery_boy_name]]<br>Email : [[delivery_boy_email]]<br>Téléphone : [[delivery_boy_phone]]',
            ],
            'registration_from_system_email_to_deliveryboy' => [
                'subject' => 'Bienvenue dans l\'équipe de livraison de [[store_name]] !',
                'default_text' => 'Bonjour [[delivery_boy_name]],<br><br>Votre compte livreur a été créé avec succès. Votre mot de passe temporaire est : <strong>[[delivery_boy_password]]</strong>',
            ],
            'email_verification_seller' => [
                'subject' => 'Vérifiez votre adresse email pour activer votre compte vendeur sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email et activer votre compte vendeur sur [[store_name]] :<br><br>[[verify_email_button]]',
            ],
            'email_verification_customer' => [
                'subject' => 'Confirmez votre adresse email pour finaliser votre inscription sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Veuillez confirmer votre adresse email pour finaliser votre inscription sur [[store_name]] :<br><br>[[verify_email_button]]',
            ],
            'password_reset_email_to_all' => [
                'subject' => 'Réinitialisation de mot de passe - [[store_name]]',
                'default_text' => 'Bonjour,<br><br>Votre code de réinitialisation de mot de passe pour [[store_name]] est : <strong>[[code]]</strong> pour l\'adresse [[user_email]].',
            ],
            'order_placed_email_to_admin' => [
                'subject' => 'Nouvelle commande passée - [[order_code]]',
                'default_text' => 'Une nouvelle commande [[order_code]] d\'un montant de [[order_amount]] a été passée par [[customer_name]] le [[order_date]].',
            ],
            'order_placed_email_to_seller' => [
                'subject' => 'Une nouvelle commande [[order_code]] a été passée !',
                'default_text' => 'Bonjour [[admin_name]],<br><br>Une nouvelle commande [[order_code]] contenant des articles de votre boutique [[shop_name]] a été enregistrée le [[order_date]].',
            ],
            'order_placed_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] a été passée avec succès !',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Merci pour votre commande sur [[store_name]] ! Votre numéro de commande est <strong>[[order_code]]</strong> pour un montant total de <strong>[[order_amount]]</strong>.',
            ],
            'order_confirmed_email_to_admin' => [
                'subject' => 'Commande confirmée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] d\'un montant de [[order_amount]] a été confirmée.',
            ],
            'order_confirmed_email_to_seller' => [
                'subject' => 'Commande confirmée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] pour votre boutique a été confirmée.',
            ],
            'order_confirmed_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] a été confirmée !',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Excellente nouvelle ! Votre commande <strong>[[order_code]]</strong> a été confirmée et est en cours de préparation.',
            ],
            'order_picked_up_email_to_admin' => [
                'subject' => 'Commande récupérée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] a été prise en charge par le livreur.',
            ],
            'order_picked_up_email_to_seller' => [
                'subject' => 'Commande récupérée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] a été prise en charge pour expédition.',
            ],
            'order_picked_up_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] a été prise en charge !',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre commande <strong>[[order_code]]</strong> a été récupérée par notre service de livraison.',
            ],
            'order_on_the_way_email_to_admin' => [
                'subject' => 'Commande en cours de livraison - [[order_code]]',
                'default_text' => 'La commande [[order_code]] est actuellement en cours de livraison.',
            ],
            'order_on_the_way_email_to_seller' => [
                'subject' => 'Commande en cours de livraison - [[order_code]]',
                'default_text' => 'La commande [[order_code]] est en cours d\'acheminement vers le client.',
            ],
            'order_on_the_way_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] est en cours de livraison !',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre commande <strong>[[order_code]]</strong> est en cours d\'acheminement vers votre adresse de livraison.',
            ],
            'order_delivered_email_to_admin' => [
                'subject' => 'Commande livrée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] a été livrée au client avec succès.',
            ],
            'order_delivered_email_to_seller' => [
                'subject' => 'Commande livrée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] pour votre boutique a été livrée.',
            ],
            'order_delivered_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] a été livrée !',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre commande <strong>[[order_code]]</strong> a été livrée avec succès. Merci d\'avoir acheté sur [[store_name]] !',
            ],
            'order_cancelled_email_to_admin' => [
                'subject' => 'Commande annulée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] a été annulée.',
            ],
            'order_cancelled_email_to_seller' => [
                'subject' => 'Commande annulée - [[order_code]]',
                'default_text' => 'La commande [[order_code]] pour votre boutique a été annulée.',
            ],
            'order_cancelled_email_to_customer' => [
                'subject' => 'Votre commande [[order_code]] a été annulée',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Nous vous informons que votre commande <strong>[[order_code]]</strong> a été annulée.',
            ],
            'order_paid_email_to_admin' => [
                'subject' => 'Paiement reçu pour la commande [[order_code]]',
                'default_text' => 'Le paiement d\'un montant de [[order_amount]] pour la commande [[order_code]] a été reçu.',
            ],
            'order_paid_email_to_seller' => [
                'subject' => 'Paiement reçu pour la commande [[order_code]]',
                'default_text' => 'Le paiement pour la commande [[order_code]] a été validé.',
            ],
            'order_paid_email_to_customer' => [
                'subject' => 'Paiement reçu pour votre commande [[order_code]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Nous avons bien reçu votre paiement pour la commande <strong>[[order_code]]</strong>.',
            ],
            'refund_request_email_to_admin' => [
                'subject' => 'Nouvelle demande de remboursement pour la commande [[order_code]]',
                'default_text' => 'Une nouvelle demande de remboursement a été soumise pour la commande [[order_code]].',
            ],
            'refund_request_email_to_seller' => [
                'subject' => 'Demande de remboursement pour la commande [[order_code]]',
                'default_text' => 'Une demande de remboursement a été effectuée pour la commande [[order_code]].',
            ],
            'refund_request_email_to_customer' => [
                'subject' => 'Demande de remboursement reçue pour la commande [[order_code]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre demande de remboursement pour la commande <strong>[[order_code]]</strong> a bien été reçue et est en cours d\'examen.',
            ],
            'refund_accepted_by_admin_email_to_admin' => [
                'subject' => 'Demande de remboursement acceptée pour la commande [[order_code]]',
                'default_text' => 'La demande de remboursement pour la commande [[order_code]] a été acceptée par l\'administrateur.',
            ],
            'refund_accepted_by_seller_email_to_admin' => [
                'subject' => 'Demande de remboursement pour [[order_code]] acceptée par [[shop_name]]',
                'default_text' => 'Le vendeur [[shop_name]] a accepté la demande de remboursement pour la commande [[order_code]].',
            ],
            'refund_accepted_by_admin_email_to_seller' => [
                'subject' => 'Demande de remboursement acceptée pour la commande [[order_code]]',
                'default_text' => 'La demande de remboursement pour la commande [[order_code]] a été acceptée par l\'administrateur.',
            ],
            'refund_accepted_by_seller_email_to_seller' => [
                'subject' => 'Demande de remboursement acceptée pour la commande [[order_code]]',
                'default_text' => 'Vous avez accepté la demande de remboursement pour la commande [[order_code]].',
            ],
            'refund_request_accepted_email_to_customer' => [
                'subject' => 'Remboursement accepté pour votre commande [[order_code]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Excellente nouvelle ! Votre demande de remboursement pour la commande <strong>[[order_code]]</strong> a été acceptée.',
            ],
            'refund_denied_by_admin_email_to_admin' => [
                'subject' => 'Demande de remboursement refusée pour la commande [[order_code]]',
                'default_text' => 'La demande de remboursement pour la commande [[order_code]] a été refusée.',
            ],
            'refund_denied_by_seller_email_to_admin' => [
                'subject' => 'Remboursement pour [[order_code]] refusé par le vendeur [[shop_name]]',
                'default_text' => 'Le vendeur [[shop_name]] a refusé la demande de remboursement pour la commande [[order_code]].',
            ],
            'refund_denied_by_admin_email_to_seller' => [
                'subject' => 'Demande de remboursement refusée pour la commande [[order_code]]',
                'default_text' => 'La demande de remboursement pour la commande [[order_code]] a été refusée par l\'administrateur.',
            ],
            'refund_denied_by_seller_email_to_seller' => [
                'subject' => 'Demande de remboursement refusée pour la commande [[order_code]]',
                'default_text' => 'Vous avez refusé la demande de remboursement pour la commande [[order_code]].',
            ],
            'refund_request_denied_email_to_customer' => [
                'subject' => 'Demande de remboursement refusée pour la commande [[order_code]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Nous vous informons que votre demande de remboursement pour la commande <strong>[[order_code]]</strong> a été refusée.',
            ],
            'seller_payout_request_email_to_admin' => [
                'subject' => 'Demande de virement vendeur - [[shop_name]]',
                'default_text' => 'La boutique [[shop_name]] a soumis une demande de virement d\'un montant de [[amount]].',
            ],
            'seller_payout_request_email_to_seller' => [
                'subject' => 'Demande de virement reçue sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Votre demande de virement d\'un montant de [[amount]] a bien été transmise à l\'équipe comptable.',
            ],
            'seller_payout_email_to_admin' => [
                'subject' => 'Virement vendeur effectué – [[shop_name]]',
                'default_text' => 'Le paiement vers la boutique [[shop_name]] d\'un montant de [[amount]] a été traité.',
            ],
            'seller_payout_email_to_seller' => [
                'subject' => 'Confirmation de paiement de [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Votre paiement d\'un montant de [[amount]] a été effectué avec succès.',
            ],
            'email_verification_for_registration_seller' => [
                'subject' => 'Vérification d\'email sur [[store_name]]',
                'default_text' => 'Bonjour,<br><br>Voici votre code de vérification d\'adresse email pour [[store_name]] : <strong>[[code]]</strong>',
            ],
            'email_verification_for_registration_customer' => [
                'subject' => 'Vérification d\'email sur [[store_name]]',
                'default_text' => 'Bonjour,<br><br>Voici votre code de vérification d\'adresse email pour [[store_name]] : <strong>[[code]]</strong>',
            ],
            'wallet_recharge_email_to_customer' => [
                'subject' => 'Votre portefeuille a été rechargé sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Votre portefeuille électronique a été rechargé d\'un montant de <strong>[[amount]]</strong> via [[payment_method]].',
            ],
            'email_update_verification_seller' => [
                'subject' => 'Vérifiez votre nouvelle adresse email pour votre compte vendeur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Voici votre code de vérification pour valider votre nouvelle adresse email : <strong>[[code]]</strong>',
            ],
            'email_update_verification_customer' => [
                'subject' => 'Confirmez votre nouvelle adresse email sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Voici votre code de vérification pour valider votre nouvelle adresse email : <strong>[[code]]</strong>',
            ],
            'change_email_verification_code_customer' => [
                'subject' => 'Vérifiez votre nouvelle adresse email sur [[store_name]]',
                'default_text' => 'Bonjour [[customer_name]],<br><br>Voici votre code de confirmation pour la mise à jour de votre email : <strong>[[code]]</strong>',
            ],
            'change_email_verification_code_seller' => [
                'subject' => 'Vérifiez votre nouvelle adresse email sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Voici votre code de confirmation pour la mise à jour de votre email : <strong>[[code]]</strong>',
            ],
            'seller_shop_approval_email' => [
                'subject' => 'Félicitations ! Votre boutique [[seller_shop_name]] a été approuvée',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Excellente nouvelle ! Votre boutique <strong>[[seller_shop_name]]</strong> a été approuvée avec succès sur [[store_name]].',
            ],
            'seller_onboarding_documents_request' => [
                'subject' => 'Action requise : Soumettez vos documents pour compléter votre inscription sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Veuillez soumettre vos documents justificatifs afin de finaliser l\'activation de votre compte vendeur sur [[store_name]].',
            ],
            'seller_documents_received_admin' => [
                'subject' => 'Nouveaux documents vendeur soumis : [[seller_shop_name]]',
                'default_text' => 'Les documents justificatifs de la boutique [[seller_shop_name]] ont été soumis pour validation.',
            ],
            'seller_application_approved' => [
                'subject' => '🎉 Félicitations ! Votre compte vendeur sur [[store_name]] est approuvé',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Votre compte vendeur est désormais pleinement activé. Vous pouvez accéder à votre espace vendeur et commencer à publier vos produits !',
            ],
            'seller_application_rejected' => [
                'subject' => 'Mise à jour concernant votre candidature vendeur sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Nous vous informons que votre demande de création de compte vendeur n\'a pas pu être approuvée pour le moment.',
            ],
            'seller_documents_submitted' => [
                'subject' => 'Vos documents vendeur sont en cours d\'examen sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Vos documents justificatifs ont bien été reçus et sont en cours d\'examen par notre équipe.',
            ],
            'seller_document_correction_required' => [
                'subject' => 'Correction requise pour votre candidature vendeur sur [[store_name]]',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Certains des documents soumis nécessitent une correction pour permettre la validation de votre compte.',
            ],
            'seller_account_suspended' => [
                'subject' => 'Votre compte vendeur sur [[store_name]] a été suspendu',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Nous vous informons que votre compte vendeur sur [[store_name]] a été suspendu. Veuillez contacter le support pour toute précision.',
            ],
            'seller_account_reactivated' => [
                'subject' => 'Votre compte vendeur sur [[store_name]] a été réactivé',
                'default_text' => 'Bonjour [[seller_name]],<br><br>Excellente nouvelle ! Votre compte vendeur sur [[store_name]] a été réactivé.',
            ],
        ];

        foreach ($frenchTemplates as $identifier => $data) {
            $hasDefaultText = Schema::hasColumn('email_templates', 'default_text');
            $hasContent = Schema::hasColumn('email_templates', 'content');

            $payload = [
                'identifier' => $identifier,
                'subject' => $data['subject'],
                'status' => 1,
            ];

            if ($hasDefaultText) {
                $payload['default_text'] = $data['default_text'];
            }
            if ($hasContent) {
                $payload['content'] = $data['default_text'];
            }

            DB::table('email_templates')
                ->updateOrInsert(['identifier' => $identifier], $payload);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting not required as this is a localization upgrade
    }
};
