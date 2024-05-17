<?php

namespace App\Http\Controllers;

use App\Models\counties;
use App\Models\PlantedTree;
use App\Models\PlantingExercise;
use App\Models\regions;
use App\Models\secretariat;
use App\Models\Teacher;
use App\Models\User;
use http\Env\Response;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class UserController extends Controller
{
    protected $startDate;
    protected $endDate;

    public function __construct()
    {
        // Initialize $startDate and $endDate in the constructor
        $this->startDate = "2023-04-05 00:00:00";
        $this->endDate = date('Y-m-d H:i:s');
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function updateUsers()
    {
        $result = User::select('tsc_no')->get();
        //return json_encode($result);
        for ($i = 0; $i < $result->count(); $i++) {
            $tscNo = $result[$i]['tsc_no'];
            $teacher = $this->getTeacherInfo($tscNo);
            $inst = $teacher[0]['institution_code'];
            if ($inst == null) {
                $inst = "NA";
                $countyCode = 0;
            } else {
                $array = str_split($inst);
                $countyCode = count($array) > 7 ? (int)($array[5] . $array[6]) : 0;
            }
            $region = Counties::select('regions_name', 'regions_id')
                ->leftJoin('tbl_regions', function ($join) use ($countyCode) {
                    $join->on('tbl_counties.regions_id', '=', 'tbl_regions.id');
                })->where('county_code', $countyCode)
                ->get();

            $regionName = count($region) == 0 ? 0 : $region[0]['regions_id'];
            echo $tscNo . " --> " . $inst . " --> $regionName\n";
            User::where('tsc_no', $tscNo)->update(['institution' => $inst,
                'county code' => $countyCode,
                'region code' => $regionName
            ]);
        }
    }

    public function store(Request $request): string
    {
        $validatedData = $request->validate([
            'tsc_no' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);
        $otp = mt_rand(1000, 9999);
        $tsc_no = $request['tsc_no'];
        $phone_no = $request['phone'];
        $response = $this->getTeacherInfo($tsc_no);

        if ($response[0]) {
            $phoneTest = '0716689703';
            $record = $response[0];
            $phone = $record['mobile'];
            $name = $record['names'];
            $email = $record['email'];
            $institutionCode = $record['station_code'];
            if ($institutionCode == null) {
                $institutionCode = "NA";
                $countyCode = null;
                $region = null;
            } else {
                $array = str_split($institutionCode);
                $countyCode = count($array) > 7 ? (int)($array[5] . $array[6]) : 0;
                $region = Counties::select('regions_id')
                    ->leftJoin('tbl_regions', function ($join) use ($countyCode) {
                        $join->on('tbl_counties.regions_id', '=', 'tbl_regions.id');
                    })->where('county_code', $countyCode)
                    ->get();
                $region = $region[0]['regions_id'];
            }
            $response = [];
            try {
            $user = User::create([
                'tsc_no' => $tsc_no,
                'phone' => $phone?:$phone_no,
                'name' => $name,
                'email' => $email?:null,
                'otp' => $otp,
                'institution' => $institutionCode,
                'county code' => $countyCode,
                'region code' => $region
            ]);
            }catch (QueryException $e){
                $response['code'] = 100;
                $response['error'] = $e->getMessage();
                $response['message'] = $e->getCode() == 23000? "Record already exists. Go to login" :"record cannot be added to database";
                return json_encode($response);
            }
            if ($user) {
                $api_key = 'REMOVED_ROTATE_CREDENTIAL';
                $mobile = $phone;
                $message = 'Your OTP for TSC Miti account activation is: '.$otp;

                $url = 'http://192.168.100.226/integrate/s-p/temp-sms?' . http_build_query([
                        'api_key' => $api_key,
                        'mobile' => $mobile,
                        'message' => $message,
                    ]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                 curl_exec($ch);
                 curl_close($ch);
                $response["code"] = 0;
                $response['record'] = $user;
                return json_encode($response);
            } else {
                $response['code'] = 101;
                $response['message'] = "failed to create user";
                return json_encode($response);
            }
        } else {
            $response['code'] = 102;
            $response['message'] = "invalid tsc number";
            return json_encode($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /*
     * function to fetch records from the register using a tsc number
     * */

    public function getTeacherInfo($tsc_no)
    {
        $id = (int)$tsc_no;
       // $url = "https://tsconline.tsc.go.ke/api/teacher-register/teacher-register-details-to-edms?api_key=REMOVED_ROTATE_CREDENTIAL&Id_Number=$id";
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
       // $response = file_get_contents($url, true, $context);
       // $response = json_decode($response,true);
        //if (!$response[0]) {
            $url = "https://tpay.tsc.go.ke/tp/v1/api/payroll/?api_key=REMOVED_ROTATE_CREDENTIAL&tsc_no=$tsc_no";
            $response = json_decode(file_get_contents($url, true, $context), true);
            if($response){
                $url = "https://tpay.tsc.go.ke/tp/v1/api/user-contact/?api_key=REMOVED_ROTATE_CREDENTIAL&tsc_no=".$tsc_no;
                $contacts =json_decode(file_get_contents($url, true, $context), true);
                $response[0]['mobile'] = $contacts["phone_number"];
                $response[0]['email'] = $contacts["email"];
            }

//        }else{
//            $response[0]['names'] = $response[0]['surname'] . " " . $response[0]['first_name'] . " " . $response[0]['last_name'];
//            $response[0]['station_code'] = $response[0]['institution_code'];
//
//        }

        if ($response === false) {
            return false;
        } else {
            if ($response) {
                return $response;
            } else {
                return [false];
            }
        }

    }

    /*
     * function to fetch otp from db*/

    public function confirmOtp(Request $request)
    {
        $tsc_no = $request['tsc_no'];
        $confirmOtp = $request['otp'];
        $otp = User::where('tsc_no', $tsc_no)->value('otp');
        if ($otp) {
            //return $otp;
            if ($confirmOtp == $otp) {
                return 0;
            } else {
                return 101;
            }
        } else {
            return 102;
        }

    }

    /*
     * function to set password*/

    public function setPassword(Request $request)
    {
        $password = $request['password'];
        $tscNo = $request['tsc_no'];
        $result = User::where('tsc_no', $tscNo)->update([
            'password' => $password,
            'activation_status' => 1
        ]);
        return $result;
    }

    /*
     * function to login*/

    public function login(Request $request): JsonResponse
    {
        if (isset($request['tsc_no']) && isset($request['password'])) {
            $pass = $request['password'];
            $tscNo = $request['tsc_no'];
            $result = User::select('tsc_no', 'password', 'name')->where('tsc_no', $tscNo)->where('password', $pass)->get();
            if ($result->count() > 0) {
//                $totalTrees = $this->countAllTrees();
//                $myTrees = $this->countMyTrees($tscNo);
                $result['code'] = 0;
//                $result['total trees'] = $totalTrees;
//                $result['my trees'] = $myTrees;
//                $result['leaderboard'] = $this->countTreesOfParticipants();
//                $result['regional performance'] = $this->calculateRegionalPerformance();
                return response()->json($result);
            } else {
                $response = array('code' => 102);
                return response()->json($response);
            }
        } else {
            $response = array('code' => 101);
            return response()->json($response);
        }
    }

    /*
     * function to fetch dashboard details*/

    public function updateDashboard(Request $request): JsonResponse
    {
        //echo $request['query'];
        //$this->startDate = $request['start_date'];
        //$this->endDate = $request['end_date'];
        $totalTrees = $this->countAllTrees();
        $myTrees = $this->countMyTrees($request['query']);
        $leaderboard = $this->countTreesOfParticipants();
        $regionalPerformance = $this->calculateRegionalPerformance();
        return response()->json(['total trees' => $totalTrees,
            'my trees' => $myTrees,
            'leaderboard' => $leaderboard,
            'regional performance' => $regionalPerformance]);
    }

    /*
     * function to fetch the total number of trees that have been planted*/

    public function countAllTrees()
    {
        $result = (int)PlantedTree::whereBetween('planted_trees.created_at', [$this->startDate, $this->endDate])->sum('no_of_trees');
        return $result;
    }

    /*
     * function to count trees planted by a specific user*/

    public function countMyTrees($tscNo)
    {
        $result = (int)PlantedTree::where('planter', $tscNo)->whereBetween('planted_trees.created_at', [$this->startDate, $this->endDate])->sum('no_of_trees');
        return $result;
    }

    /*
     * function to fetch all the exercises the user has participated.
     * */

    public function fetchParticipatedExercises(Request $request)
    {
        $tscNo = $request['query'];
        return PlantedTree::selectRaw("COALESCE(`exercise name`, 'other') AS 'exercise name', cast(COALESCE(sum(no_of_trees), 0) as unsigned) AS count")
        ->leftJoin('planting_exercises', function($join){
            $join->on('planted_trees.exercise_id', '=', 'planting_exercises.id');
        })
        ->where('planter', $tscNo)->groupBy('exercise_id', 'exercise name')->distinct()->get();
    }

    /*
     * function to return number of trees each participant has planted*/

    public function countTreesOfParticipants()
    {
        $response = PlantedTree::selectRaw('name, cast(COALESCE(sum(no_of_trees), 0) as unsigned) AS count')
    ->leftJoin('users', function ($join) {
        $join->on('planter', '=', 'tsc_no');
    })
    ->whereBetween('planted_trees.created_at', [$this->startDate, $this->endDate])
    ->groupBy('name', 'planter')
    ->orderByDesc('count') // Use orderByDesc for descending order
    ->limit(50)
    ->get();
        return $response;
    }

    /*
     * function to calculate regional performance
     * */

    public function calculateRegionalPerformance()
    {
        $result = regions::selectRaw('tbl_regions.id, tbl_regions.regions_name, cast(COALESCE(sum(CASE WHEN planted_trees.created_at BETWEEN ? AND ? THEN planted_trees.no_of_trees ELSE 0 END), 0) as unsigned) AS count',[$this->startDate, $this->endDate])
            ->leftJoin('users', function ($join) {
                $join->on('tbl_regions.id', '=', 'users.region code');
            })->leftJoin('planted_trees', function ($join) {
                $join->on('users.tsc_no', '=', 'planted_trees.planter');
            })->groupBy('tbl_regions.regions_name', 'tbl_regions.id')
            ->orderBy('tbl_regions.id', 'asc')->get();
        return $result;
    }

    /*
     * function to calculate secretariat performance*/

    public function calculateSecretariatPerformance(){
        $result = secretariat::selectRaw('`station name`,cast(COALESCE(sum(no_of_trees), 0) as unsigned) AS treecount')
            ->leftJoin('planted_trees', function ($join){
                $join->on('secretariat staff.payrollnum', '=', 'planted_trees.planter');
            })->whereBetween('planted_trees.created_at', [$this->startDate, $this->endDate])->groupBy('station name')->having('treecount', '>', 0)-> get();

        return $result;
    }


    /*
     * function to calculate participation of teachers per county*/
    public function calculateTeacherPerformancePerCounty(){
        $result = Teacher::selectRaw('`County-Name` AS `station name`, cast(COALESCE(sum(no_of_trees), 0) as unsigned) as treecount')
            ->leftJoin('planted_trees', function ($join){
                $join->on('teachers.Tsc No', '=', 'planted_trees.planter');
            })->whereBetween('planted_trees.created_at', [$this->startDate, $this->endDate])->groupBy('County-Name')->having('treecount', '>', 0)->get();
        return $result;
    }

    public function  updateSecretariatStations(){

    }
}
