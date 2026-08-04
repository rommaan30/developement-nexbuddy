<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->json('enquiry_interests')->nullable()->after('instructions');
        });

        $software = $this->softwareDictionary();
        $medical = $this->medicalDictionary();

        $nexgenoUserId = DB::table('users')
            ->where('email', 'admin@nexgeno.in')
            ->value('id');

        $rajeshUserId = DB::table('users')
            ->where('email', 'rajeshdeshmukh108@gmail.com')
            ->value('id');

        if ($nexgenoUserId !== null) {
            DB::table('ext_chatbots')
                ->where('user_id', $nexgenoUserId)
                ->update(['enquiry_interests' => json_encode($software)]);
        }

        if ($rajeshUserId !== null) {
            DB::table('ext_chatbots')
                ->where('user_id', $rajeshUserId)
                ->update(['enquiry_interests' => json_encode($medical)]);
        }

        // Any remaining bots inherit the previous global (software) vocabulary
        // so behaviour stays backward compatible until owners customise it.
        DB::table('ext_chatbots')
            ->whereNull('enquiry_interests')
            ->update(['enquiry_interests' => json_encode($software)]);
    }

    public function down(): void
    {
        Schema::table('ext_chatbots', function (Blueprint $table) {
            $table->dropColumn('enquiry_interests');
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function softwareDictionary(): array
    {
        return [
            'Website Development'    => ['website development', 'web development', 'website', 'web app'],
            'Mobile App Development' => ['mobile app development', 'mobile application', 'mobile app', 'android app', 'ios app'],
            'AI Chatbot'             => ['ai chatbot', 'ai bot', 'chatbot', 'chat bot'],
            'Business Automation'    => ['business automation', 'process automation', 'automation'],
            'Customer Support'       => ['customer support', 'customer service'],
            'Lead Generation'        => ['lead generation', 'generate leads'],
            'Enterprise Solution'    => ['enterprise solution', 'enterprise plan', 'enterprise'],
            'Technical Support'      => ['technical support', 'tech support'],
            'CRM'                    => ['crm'],
            'HRMS'                   => ['hrms', 'hr management'],
            'POS'                    => ['pos system', 'point of sale', 'pos'],
            'Pricing'                => ['pricing', 'price', 'quotation', 'quote', 'cost'],
            'Demo'                   => ['product demo', 'demo', 'trial'],
            'Consultation'           => ['consultation', 'consulting', 'consultancy'],
            'Partnership'            => ['partnership', 'partner with', 'reseller'],
            'Sales'                  => ['buy', 'purchase', 'subscription', 'ecommerce', 'e-commerce', 'ecomm', 'ecom', 'digital marketing'],
            'Support'                => ['issue', 'help', 'support'],
            'Callback'               => ['call me', 'callback', 'reach me', 'contact me'],
            'API Integration'        => ['api integration', 'rest api', 'api'],
            'WhatsApp Integration'   => ['whatsapp integration', 'whatsapp api', 'whatsapp'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function medicalDictionary(): array
    {
        return [
            'General Consultation' => ['general consultation', 'consultation', 'consult', 'doctor appointment'],
            'Eye Check-up'         => ['eye check-up', 'eye checkup', 'eye check up', 'eye examination', 'vision test'],
            'Cataract'             => ['cataract', 'cataract surgery'],
            'LASIK'                => ['lasik', 'laser eye surgery', 'laser vision'],
            'Retina'               => ['retina', 'retinal', 'retina treatment'],
            'Glaucoma'             => ['glaucoma'],
            'Dry Eyes'             => ['dry eyes', 'dry eye'],
            'Diabetic Eye Care'    => ['diabetic eye', 'diabetic retinopathy', 'diabetes eye'],
            'Appointment'          => ['appointment', 'book appointment', 'schedule visit'],
        ];
    }
};
