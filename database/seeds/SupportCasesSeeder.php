<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportCasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old data for idempotent seeding
        DB::table('case_escalation_rules')->delete();
        DB::table('case_resolution_steps')->delete();
        DB::table('case_required_fields')->delete();
        DB::table('case_question_variants')->delete();
        DB::table('support_cases')->delete();
        DB::table('support_categories')->delete();

        // 1. Seed Categories
        $categories = [
            ['code' => 'PD', 'name' => 'Product Discovery', 'description' => 'Help finding products', 'display_order' => 1],
            ['code' => 'PI', 'name' => 'Product Information', 'description' => 'Dimensions, materials, stock', 'display_order' => 2],
            ['code' => 'AC', 'name' => 'Account & Login', 'description' => 'Registration, passwords, access', 'display_order' => 3],
            ['code' => 'CK', 'name' => 'Cart & Checkout', 'description' => 'Cart issues, discounts, checkout', 'display_order' => 4],
            ['code' => 'PY', 'name' => 'Payment', 'description' => 'Declined payments, receipts', 'display_order' => 5],
            ['code' => 'OR', 'name' => 'Orders', 'description' => 'Status, cancellations, missing items', 'display_order' => 6],
            ['code' => 'DL', 'name' => 'Delivery', 'description' => 'Tracking, delays, damaged packages', 'display_order' => 7],
            ['code' => 'RT', 'name' => 'Returns & Refunds', 'description' => 'Eligibility, status, defective items', 'display_order' => 8],
            ['code' => 'PR', 'name' => 'Interior Design & Pro Services', 'description' => 'Consultations, bulk orders', 'display_order' => 9],
            ['code' => 'TC', 'name' => 'Technical Problems', 'description' => 'Bugs, slow site, missing images', 'display_order' => 10],
            ['code' => 'CP', 'name' => 'Complaints', 'description' => 'Service issues, vendor issues', 'display_order' => 11],
            ['code' => 'SC', 'name' => 'Privacy & Security', 'description' => 'Data access, fraud', 'display_order' => 12],
            ['code' => 'HS', 'name' => 'Human Support', 'description' => 'Speak to an agent', 'display_order' => 13],
        ];

        foreach ($categories as $cat) {
            DB::table('support_categories')->insert($cat);
        }
        
        $catIds = DB::table('support_categories')->pluck('id', 'code')->toArray();

        // 1.1 Seed Category & System Message Translations
        $categoryTranslations = [
            'Product Discovery' => [
                'fr' => 'Découverte de produits',
                'sa' => 'اكتشاف المنتجات',
                'ma' => 'اكتشاف المنتجات',
                'ar' => 'اكتشاف المنتجات',
            ],
            'Product Information' => [
                'fr' => 'Informations produit',
                'sa' => 'معلومات المنتج',
                'ma' => 'معلومات المنتج',
                'ar' => 'معلومات المنتج',
            ],
            'Account & Login' => [
                'fr' => 'Compte & Connexion',
                'sa' => 'الحساب والدخول',
                'ma' => 'الحساب والدخول',
                'ar' => 'الحساب والدخول',
            ],
            'Cart & Checkout' => [
                'fr' => 'Panier & Commande',
                'sa' => 'السلة والإنهاء',
                'ma' => 'السلة والإنهاء',
                'ar' => 'السلة والإنهاء',
            ],
            'Payment' => [
                'fr' => 'Paiement',
                'sa' => 'الدفع',
                'ma' => 'الدفع',
                'ar' => 'الدفع',
            ],
            'Orders' => [
                'fr' => 'Commandes',
                'sa' => 'الطلبات',
                'ma' => 'الطلبات',
                'ar' => 'الطلبات',
            ],
            'Delivery' => [
                'fr' => 'Livraison',
                'sa' => 'التوصيل',
                'ma' => 'التوصيل',
                'ar' => 'التوصيل',
            ],
            'Returns & Refunds' => [
                'fr' => 'Retours & Remboursements',
                'sa' => 'الإرجاع والاسترداد',
                'ma' => 'الإرجاع والاسترداد',
                'ar' => 'الإرجاع والاسترداد',
            ],
            'Interior Design & Pro Services' => [
                'fr' => 'Design d\'intérieur & Pro',
                'sa' => 'التصميم الداخلي والخدمات',
                'ma' => 'التصميم الداخلي والخدمات',
                'ar' => 'التصميم الداخلي والخدمات',
            ],
            'Technical Problems' => [
                'fr' => 'Problèmes techniques',
                'sa' => 'المشاكل التقنية',
                'ma' => 'المشاكل التقنية',
                'ar' => 'المشاكل التقنية',
            ],
            'Complaints' => [
                'fr' => 'Réclamations',
                'sa' => 'الشكاوى',
                'ma' => 'الشكاوى',
                'ar' => 'الشكاوى',
            ],
            'Privacy & Security' => [
                'fr' => 'Confidentialité & Sécurité',
                'sa' => 'الخصوصية والأمان',
                'ma' => 'الخصوصية والأمان',
                'ar' => 'الخصوصية والأمان',
            ],
            'Human Support' => [
                'fr' => 'Parler à un conseiller',
                'sa' => 'التحدث مع عميل',
                'ma' => 'التحدث مع عميل',
                'ar' => 'التحدث مع عميل',
            ],
            'Hello! I am your Mayush automated assistant. How can I help you today?' => [
                'fr' => "Bonjour ! Je suis votre assistant automatisé Mayush. Comment puis-je vous aider aujourd'hui ?",
                'sa' => "مرحباً! أنا مساعد مايوش الآلي. كيف يمكنني مساعدتك اليوم؟",
                'ma' => "مرحباً! أنا مساعد مايوش الآلي. كيف يمكنني مساعدتك اليوم؟",
                'ar' => "مرحباً! أنا مساعد مايوش الآلي. كيف يمكنني مساعدتك اليوم؟",
            ],
            'I did not understand your message. Could you please clarify your idea or select one of the options below?' => [
                'fr' => "Je n'ai pas compris votre message. Pouvez-vous préciser votre demande ou sélectionner l'une des options ci-dessous ?",
                'sa' => "لم أفهم رسالتك. هل يمكنك توضيح طلبك أو اختيار أحد الخيارات أدناه؟",
                'ma' => "لم أفهم رسالتك. هل يمكنك توضيح طلبك أو اختيار أحد الخيارات أدناه؟",
                'ar' => "لم أفهم رسالتك. هل يمكنك توضيح طلبك أو اختيار أحد الخيارات أدناه؟",
            ],
            "Please select the issue you're facing:" => [
                'fr' => "Veuillez sélectionner le problème que vous rencontrez :",
                'sa' => "يرجى تحديد المشكلة التي تواجهها:",
                'ma' => "يرجى تحديد المشكلة التي تواجهها:",
                'ar' => "يرجى تحديد المشكلة التي تواجهها:",
            ],
            'Speak to a human' => [
                'fr' => 'Parler à un humain',
                'sa' => 'التحدث مع عميل',
                'ma' => 'التحدث مع عميل',
                'ar' => 'التحدث مع عميل',
            ],
            'Go back to main menu' => [
                'fr' => 'Retour au menu principal',
                'sa' => 'العودة للقائمة الرئيسية',
                'ma' => 'العودة للقائمة الرئيسية',
                'ar' => 'العودة للقائمة الرئيسية',
            ],
            'Did this solve your problem?' => [
                'fr' => 'Cela a-t-il résolu votre problème ?',
                'sa' => 'هل حل هذا مشكلتك؟',
                'ma' => 'هل حل هذا مشكلتك؟',
                'ar' => 'هل حل هذا مشكلتك؟',
            ],
            'Yes' => [
                'fr' => 'Oui',
                'sa' => 'نعم',
                'ma' => 'نعم',
                'ar' => 'نعم',
            ],
            'No' => [
                'fr' => 'Non',
                'sa' => 'لا',
                'ma' => 'لا',
                'ar' => 'لا',
            ],
        ];

        foreach ($categoryTranslations as $original => $langs) {
            $langKey = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($original)));
            foreach ($langs as $lang => $val) {
                DB::table('translations')->updateOrInsert(
                    ['lang' => $lang, 'lang_key' => $langKey],
                    ['lang_value' => $val, 'updated_at' => now()]
                );
            }
            DB::table('translations')->updateOrInsert(
                ['lang' => 'en', 'lang_key' => $langKey],
                ['lang_value' => $original, 'updated_at' => now()]
            );
        }

        // 2. Load and Parse LIVE_CHAT_BOT_ANSWERS.md dynamically
        $answersPath = base_path('LIVE_CHAT_BOT_ANSWERS.md');
        if (!file_exists($answersPath)) {
            throw new \Exception("LIVE_CHAT_BOT_ANSWERS.md file not found at " . $answersPath);
        }

        $content = file_get_contents($answersPath);
        $sections = explode("\n## ", $content);

        foreach ($sections as $section) {
            // Match case code pattern: e.g., `PD-001` — Find a product by category
            if (preg_match('/^`([A-Z]{2}-\d{3})`[\s—\-–]+([^\r\n]+)/', $section, $headerMatches)) {
                $caseCode = $headerMatches[1];
                $caseName = trim($headerMatches[2]);
                
                // Determine category prefix
                $categoryCode = substr($caseCode, 0, 2);
                
                // Find category ID
                if (!isset($catIds[$categoryCode])) {
                    continue; // Skip if category is not seeded
                }
                $categoryId = $catIds[$categoryCode];
                
                // Extract Case Explanation / Description
                $description = '';
                if (preg_match('/###\s+(?:Case explanation|Explanation)[^\r\n]*\r?\n\r?\n(.*?)(?=\r?\n###|\r?\n---|$)/s', $section, $descMatches)) {
                    $description = trim($descMatches[1]);
                }
                
                if (empty($description)) {
                    $description = "Support for " . strtolower($caseName) . ".";
                }
                
                // Insert support case
                $caseId = DB::table('support_cases')->insertGetId([
                    'category_id' => $categoryId,
                    'case_code' => $caseCode,
                    'name' => $caseName,
                    'description' => $description,
                    'eligible_user_types' => 'all',
                    'priority' => 'normal',
                    'department' => 'Support',
                    'status' => 'active',
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Extract Questions for variants
                if (preg_match('/###\s+(?:Possible customer questions|Possible questions)[^\r\n]*\r?\n\r?\n(.*?)(?=\r?\n###|\r?\n---|$)/s', $section, $questionsMatches)) {
                    $lines = explode("\n", $questionsMatches[1]);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, '*')) {
                            $question = trim(substr($line, 1));
                            if (!empty($question)) {
                                DB::table('case_question_variants')->insert([
                                    'case_id' => $caseId,
                                    'language' => 'en',
                                    'question' => $question,
                                    'keywords' => json_encode([]),
                                    'weight' => 1,
                                    'status' => 'active',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
                
                // Extract Required Information (for required fields)
                if (preg_match('/###\s+Required information\r?\n\r?\n(.*?)(?=\r?\n###|\r?\n---|$)/s', $section, $fieldsMatches)) {
                    $lines = explode("\n", $fieldsMatches[1]);
                    $displayOrder = 1;
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, '*')) {
                            $fieldName = trim(substr($line, 1));
                            if (!empty($fieldName) && !str_starts_with(strtolower($fieldName), 'optional')) {
                                $fieldKey = str_replace(' ', '_', strtolower($fieldName));
                                DB::table('case_required_fields')->insert([
                                    'case_id' => $caseId,
                                    'field_key' => $fieldKey,
                                    'label' => $fieldName,
                                    'field_type' => 'text',
                                    'required' => true,
                                    'bot_prompt' => 'Please provide the ' . strtolower($fieldName) . '.',
                                    'display_order' => $displayOrder++,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // 3. Seed specific resolution steps and rules for MVP priority cases
        $caseIds = DB::table('support_cases')->pluck('id', 'case_code')->toArray();

        // Seed specific required fields for MVP priorities (insuring exact naming alignment)
        $overrideFields = [
            // OR-002: Order status inquiry requires order reference
            ['case_id' => $caseIds['OR-002'] ?? null, 'field_key' => 'order_reference', 'label' => 'Order Reference', 'field_type' => 'text', 'bot_prompt' => 'Please provide your order reference number (e.g. ORD-1234).'],
            // OR-006: Cancel order
            ['case_id' => $caseIds['OR-006'] ?? null, 'field_key' => 'order_reference', 'label' => 'Order Reference', 'field_type' => 'text', 'bot_prompt' => 'Please provide the order reference for the order you wish to cancel.'],
            // PY-001: Payment declined
            ['case_id' => $caseIds['PY-001'] ?? null, 'field_key' => 'order_reference', 'label' => 'Order Reference', 'field_type' => 'text', 'bot_prompt' => 'Could you provide the order reference you were trying to pay for?'],
            // DL-003: Delivery estimate
            ['case_id' => $caseIds['DL-003'] ?? null, 'field_key' => 'order_reference', 'label' => 'Order Reference', 'field_type' => 'text', 'bot_prompt' => 'Please provide your order reference so I can check the delivery status.'],
            // RT-008: Refund status
            ['case_id' => $caseIds['RT-008'] ?? null, 'field_key' => 'order_reference', 'label' => 'Order Reference', 'field_type' => 'text', 'bot_prompt' => 'Please provide the order reference for your returned item.'],
        ];

        foreach ($overrideFields as $f) {
            if ($f['case_id']) {
                // Remove the general auto-parsed fields for these override cases first
                DB::table('case_required_fields')->where('case_id', $f['case_id'])->delete();
                DB::table('case_required_fields')->insert($f);
            }
        }

        // Seed specific resolution steps
        $steps = [
            // OR-002
            ['case_id' => $caseIds['OR-002'] ?? null, 'step_order' => 1, 'step_type' => 'action', 'action_key' => 'getOrderStatus', 'success_transition' => 'confirm', 'message_template' => null],
            // OR-006
            ['case_id' => $caseIds['OR-006'] ?? null, 'step_order' => 1, 'step_type' => 'action', 'action_key' => 'checkCancelEligibility', 'success_transition' => 'confirm', 'message_template' => null],
            // PY-001
            ['case_id' => $caseIds['PY-001'] ?? null, 'step_order' => 1, 'step_type' => 'action', 'action_key' => 'checkPaymentStatus', 'success_transition' => 'confirm', 'message_template' => null],
            // DL-003
            ['case_id' => $caseIds['DL-003'] ?? null, 'step_order' => 1, 'step_type' => 'action', 'action_key' => 'getDeliveryEstimate', 'success_transition' => 'confirm', 'message_template' => null],
            // RT-008
            ['case_id' => $caseIds['RT-008'] ?? null, 'step_order' => 1, 'step_type' => 'action', 'action_key' => 'getRefundStatus', 'success_transition' => 'confirm', 'message_template' => null],
        ];

        foreach ($steps as $s) {
            if ($s['case_id']) {
                DB::table('case_resolution_steps')->insert($s);
            }
        }
        
        // Seed specific escalation rules
        $escalations = [
            ['case_id' => $caseIds['PY-003'] ?? null, 'rule_type' => 'immediate', 'threshold' => 0, 'priority' => 'critical', 'target_department' => 'Finance', 'handoff_message' => 'Escalating suspected charged-no-order.'],
            ['case_id' => $caseIds['DL-008'] ?? null, 'rule_type' => 'immediate', 'threshold' => 0, 'priority' => 'high', 'target_department' => 'Logistics', 'handoff_message' => 'Escalating marked delivered but not received.'],
            ['case_id' => $caseIds['HS-001'] ?? null, 'rule_type' => 'immediate', 'threshold' => 0, 'priority' => 'normal', 'target_department' => 'Support', 'handoff_message' => 'Direct agent request.'],
        ];
        
        foreach ($escalations as $e) {
            if ($e['case_id']) {
                DB::table('case_escalation_rules')->insert($e);
            }
        }
    }
}
