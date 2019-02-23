<?php

use Illuminate\Database\Seeder;
use App\AccountSecurity;

class AccountSecuritySeed extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        AccountSecurity::truncate();
        //create security questions
        $securityQuestions = [
            'What is your pets name?',
            'What is your favorite television show?',
            'What is your mother\'s maiden name?',
            'What is your favorite food?',
            'Who is your favorite superhero?',
        ];

        foreach($securityQuestions as $questions) {
            $securityQuestionPrefix = 'sec_'.date('Y').rand(100, 999);
            $seed = [
                'sec_id' => $securityQuestionPrefix,
                'value' => $questions,
                'description' => 'security question',
            ];

            AccountSecurity::create($seed);
        }


    }

}