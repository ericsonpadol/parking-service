<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AccountSecurity;
use App\Copywrite;
use App\Http\Requests;

class AccountSecurityController extends Controller
{
    /**
     *  Generate EULA Template
     *
     * @return html_view
     */
    public function generateEula() {
        $eulaCopywrite = [
            'eula_title' => Copywrite::EULA_TITLE,
            'eula_header' => Copywrite::EULA_HEADER,
            'eula_header_desc' => Copywrite::EULA_HEADER_DESC,
            'eula_license_header' => Copywrite::EULA_LICENSE_HEADER,
            'eula_license_content' => Copywrite::EULA_LICENSE_CONTENT,
            'eula_restriction_header' => Copywrite::EULA_RESTRICTION_HEADER,
            'eula_restriction_content' => Copywrite::EULA_RESTRICTION_CONTENT,
            'eula_mods_to_application_header' => Copywrite::EULA_MODS_TO_APPLICATION_HEADER,
            'eula_mods_to_application_content' => Copywrite::EULA_MODS_TO_APPLICATION_CONTENT,
            'eula_term_and_termination_header' => Copywrite::EULA_TERM_AND_TERMINATION_HEADER,
            'eula_term_and_termination_content' => Copywrite::EULA_TERM_AND_TERMINATION_CONTENT,
            'eula_severability_header' => Copywrite::EULA_TERM_AND_TERMINATION_HEADER,
            'eula_severability_content' => Copywrite::EULA_SEVERABILITY_CONTENT,
            'eula_amends_agreements_header' => Copywrite::EULA_AMENDMENTS_AGREEMENT_HEADER,
            'eula_amends_agreements_content' => Copywrite::EULA_AMENDMENTS_AGREEMENT_CONTENT,
            'eula_contact_info_header' => Copywrite::EULA_CONTACT_INFORMATION_HEADER,
            'eula_contact_info_content' => Copywrite::EULA_CONTACT_INFORMATION_CONTENT
        ];

        return view('eula', $eulaCopywrite);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listSecurityQuestions = AccountSecurity::all();

        return response()->json([
            'data' => $listSecurityQuestions,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //recover the security questions
        $oAccountSec = new AccountSecurity();

        $userSecQues = $oAccountSec->getAccountSecurityQuestions($id);

        return response()->json($userSecQues);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
