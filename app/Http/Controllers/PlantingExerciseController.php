<?php

namespace App\Http\Controllers;

use App\Models\participant;
use App\Models\PlantingExercise;
use App\Models\PlantedTree;
use App\Models\User;
use Illuminate\Database\QueryException;
//use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\File;


class PlantingExerciseController extends Controller
{
    /*
     * function to create an entry for a new planting exercise
     */
    public function registerExercise(Request $request){
        $exerciseName = $request['exercise'];
        $initiator = $request['initiator'];
        $initiatorContact = $request['initiator contact'];
        $location = $request['location'];
        $target = $request['target seedlings'];
        $deviation = -$target;

        $exercise = PlantingExercise::create([
            'exercise name' => $exerciseName,
            'initiator' => $initiator,
            'initiator contact' => $initiatorContact,
            'location' => $location,
            'target seedlings' => $target,
            'deviation' => $deviation
        ]);
        if($exercise){
            $path = storage_path('app/public/images/'.$exerciseName);
            File::makeDirectory($path, $mode = 0777, true, true);
            return 1;
        }else{
            return 0;
        }


    }

    public function startExercise(Request $request){
        $exerciseName = $request['exercise'];
        $initiator = $request['initiator'];
        $start = $request['start'];
        $exerciseStatus = PlantingExercise::where('exercise name', $exerciseName)
            ->where('initiator', $initiator)->value('status');

        if ($exerciseStatus ===0){
            return PlantingExercise::where('exercise name', $exerciseName)
                ->where('initiator', $initiator)
                ->update(['start' => $start, 'status' => 1]);
        }elseif ($exerciseStatus === 2){
            return 102;
        }else{
            return 101;
        }
    }

    /*
     * function to close a planting exercise*/

    public function closeExercise(Request $request){
        $exerciseName = $request['exercise'];
        $initiator = $request['initiator'];
        $end = $request['end'];
        $exerciseStatus = PlantingExercise::where('exercise name', $exerciseName)
            ->where('initiator', $initiator)->value('status');

        if ($exerciseStatus === 1){
            return PlantingExercise::where('exercise name', $exerciseName)
                ->where('initiator', $initiator)
                ->update(['end' => $end, 'status' => 2]);
        }elseif ($exerciseStatus === 2){
            return 102;
        }else{
            return 101;
        }
    }

    /*
     * function to search a planting exercise*/

    public function searchPlantingExercise(Request $request){
        $query = $request->query('query');

        $exercise = PlantingExercise::where('exercise name','like',"%$query%")
            ->where('status',1)->get();

        if (!$exercise->isEmpty()){
        return $exercise;
        }else{
            return 0;
        }
    }


    public function fetchPlantingExercisesByInitiator(Request $request){
        $query = $request->query('query');

        $exercise = PlantingExercise::where('initiator',$query)->get();

        if (!$exercise->isEmpty()){
            return $exercise;
        }else{
            return 0;
        }
    }

    /*
     * function to record participation in an exercise*/

    public function participate(Request $request){
        $exercise = $request['exercise'];
        $tscNo = $request['tsc_no'];
        $exerciseId = PlantingExercise::where('exercise name','like',"$exercise")
            ->where('status',1)->value('id');
        $participation = participant::where('exercise',$exerciseId)
            ->where('tsc_no',$tscNo)->get();

        /*
         * The following block prevents the participants column from incrementing if a user has already
         * registered participation fot the particular exercise before.
         * */
        if (!$participation->count()){
            participant::create([
                'tsc_no'=>$tscNo,
                'exercise'=>$exerciseId
            ]);
            return PlantingExercise::where('exercise name','like',"$exercise")
                ->where('status',1)->increment('participants');
        }else{
            return 101;
        }

    }

    /*
     * function to store a record of a tree planted*/

    public function plantATree(Request $request){
        $exercise = $request['exercise'];
        $planter = $request['tsc_no'];
        $treeName = $request['tree_name'];
        $time = $request['time'];
        $photo = $request['photo'];
        $noOfTrees = $request['no_of_trees'];

        try {
            $record = PlantedTree::create([
                'exercise_id' => $exercise,
                'planter' => $planter,
                'tree name' => $treeName,
                'photo' => $photo,
                'no_of_trees' =>$noOfTrees
            ]);

            if ($record){
                if ($exercise!=null) {
                    return PlantingExercise::where('id', $exercise)->increment('planted seedlings');
                }else{
                    return 1;
                }
            }else{
                return 101;
            }
        }catch (QueryException $q){
            return $q->getMessage();
        }


    }

    /*
     * function to upload an image to the server
     * oyyi u-name: TSCmiti
     * oyyi pass: REMOVED_ROTATE_CREDENTIAL
     * oyyi email: peterngari@tsc.go.ke
     * ori api token: REMOVED_ROTATE_CREDENTIAL
     * */

    public  function uploadImage(Request $request){
        $dir = $request->input('exercise');
        if ($request->hasFile('image')){
            $path = $request['image']->store('public/images/'.$dir);
            return response()->json(['message' => 'Image uploaded successfully', 'path' => basename($path)], 200);
        }else{
            return 101;
        }
    }
}
