<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\ParkingSpace;
use App\ParkingSpacePrice;
use App\User;
use App\Copywrite;
use App\TopUp;
use Validator;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DB;

class ParkingSpacePricingController extends Controller
{
    private $_topUp = array();

    public function __construct()
    {
        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('parkspace-pricing');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $pspacePriceList = ParkingSpacePrice::where(['user_id' => $id])->get();

        return response()->json([
            'data' => $pspacePriceList,
            'data_count' => count($pspacePriceList),
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200);
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
    public function store($parkspace, Request $request)
    {
        //check if parking space is available
        $foundPspace = ParkingSpace::find($parkspace);

        if (!$foundPspace) {
            return response()->json([
                'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'status_code' => Copywrite::STATUS_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        //store parking space pricing
        $validator = Validator::Make($request->all(), [
            'pspace_base_price' => 'required|numeric',
            'user_id' => 'required|numeric',
            'avail_start_datetime' => 'required|date',
            'avail_end_datetime' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        if ($foundPspace->user['id'] != $request->user_id) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'status_code' => Copywrite::STATUS_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        //calculate parking space calculated price
        $topups = TopUp::all();

        foreach ($topups as $key => $value) {
            switch ($value->topup_key) {
                case 'topup':
                    $this->_topUp['top_up'] = $value->topup_value;
                    break;
                case 'tax':
                    $this->_topUp['tax'] = $value->topup_value;
                    break;
            }
        }


        //compute parking space calculated price
        $pspaceCalcPrice = $this->calcParkingSpaceMarkUp($request->pspace_base_price);

        $params = [
            'pspace_base_price' => $request->pspace_base_price,
            'pspace_calc_price' => $pspaceCalcPrice,
            'avail_start_datetime' => $request->avail_start_datetime,
            'avail_end_datetime' => $request->avail_end_datetime,
            'parking_space_id' => $parkspace,
            'user_id' => $foundPspace->user['id']
        ];

        //check if parking space has already has a price
        $pspacePriceCheck = ParkingSpacePrice::where(['parking_space_id' => $parkspace])->first();

        if ($pspacePriceCheck) {
            return response()->json([
                'message' => Copywrite::PSPACE_PRICE_CHECK,
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        if (ParkingSpacePrice::create($params)) {
            return response()->json([
                'message' => Copywrite::CREATED_PSPACE_PRICE,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status_code' => Copywrite::STATUS_CODE_200
            ], Copywrite::HTTP_CODE_200);
        } else {
            return response()->json([
                'message' => Copywrite::SERVER_ERROR,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status_code' => Copywrite::STATUS_CODE_500
            ], Copywrite::HTTP_CODE_500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($parkspace, $pricing)
    {
        $parkspacePricing = ParkingSpacePrice::where([
            ['id', '=', $pricing],
            ['parking_space_id', '=', $parkspace]
        ])->get();

        if (!$parkspacePricing) {
            return response()->json([
                'message' => Copywrite::PSPACE_PRICE_CHECK,
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        return response()->json([
            'data' => $parkspacePricing,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ]);
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
    public function update(Request $request, $parkspace, $pricing)
    {
        //update pricing
        $validator = Validator::make($request->all(), [
            'pspace_base_price' => 'required|numeric',
            'avail_start_datetime' => 'required|date',
            'avail_end_datetime' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        //check if pricing entry exists
        $pSpacePricing = ParkingSpacePrice::find($pricing);

        if (!$pSpacePricing || $pSpacePricing->parking_space_id != $parkspace) {
            return response()->json([
                'message' => Copywrite::DEFAULT_NO_ENTRY_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }

        //calculate markup price
        $topups = TopUp::all();

        foreach ($topups as $key => $value) {
            switch ($value->topup_key) {
                case 'topup':
                    $this->_topUp['top_up'] = $value->topup_value;
                    break;
                case 'tax':
                    $this->_topUp['tax'] = $value->topup_value;
                    break;
            }
        }

        $pspaceMarkUp = $this->calcParkingSpaceMarkUp($request->pspace_base_price);

        $params = [
            'pspace_base_price' => $request->pspace_base_price,
            'pspace_calc_price' => $pspaceMarkUp,
            'avail_start_datetime' => $request->avail_start_datetime,
            'avail_end_datetime' => $request->avail_end_datetime
        ];

        if ($pSpacePricing->update($params)) {
            return response()->json([
                'message' => Copywrite::UPDATED_PSPACE_PRICE,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status_code' => Copywrite::STATUS_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], Copywrite::HTTP_CODE_200);
        } else {
            return response()->json([
                'message' => Copywrite::SERVER_ERROR,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status_code' => Copywrite::STATUS_CODE_500
            ], Copywrite::HTTP_CODE_500);
        }
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

    /**
     * calculate pricing markup price
     * @param Double $basePrice
     * @return Decimal $basePrice
     */
    protected function calcParkingSpaceMarkUp($basePrice)
    {
        return round($basePrice + ($basePrice * $this->_topUp['top_up']) + ($basePrice * $this->_topUp['tax']));
    }
}
