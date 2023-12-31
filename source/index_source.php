<?php

// this source code stores in /source/index_source.php
    // echo A::Hello();
    // echo C::Hello();
    // echo D::Hello();

    // Allow access from only approved hosts
    if (!Host::Allowed(Host::CHECKTOKEN))
    {
        //http_response_code(400); exit;
        header('HTTP/1.1 403 Forbidden');
        echo 'access denied';
        exit;
    }

    $input = file_get_contents('php://input');
    $inputObj = json_decode($input);

    function getTypeByName(string $type)
    {
        $class = "objects\\" . ucfirst($type);
        $object = new $class();
        return $object;
    };

    function buildObject(object $object, $instance)
    {
        foreach($instance AS $key => $value)
        {
          if (isset($object->$key))
          {
            $instance->$key = $object->$key;
          } else {
            unset($instance->$key);
          }
        }
        return $instance;
    };

    // Handle format errors
    if (!isset($inputObj->user))
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no user section detected!";
        json_encode($response);
        return;
    };

    $globalUser = $inputObj->user;

    if (!isset($inputObj->tasks))
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no task section detected!";
        if (!isset($inputObj->user)){
            $response->user = $inputObj->user;
        }
        json_encode($response);
        return;
    };

    if (is_array($inputObj->tasks) && count($inputObj->tasks) == 0)
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no one task detected!";
        if (!isset($inputObj->user)){
            $response->user = $inputObj->user;
        }
        json_encode($response);
        return;
    };


    // *
    // Collect received tasks into array 
    $tasks = [];
    foreach($inputObj->tasks AS $taskObject)
    {
      $task = Task::taskFromObject($taskObject, $inputObj->user);
      if (is_string($task))
      {
        $response = new Response();
        $response->status = 1;
        $response->message = "The problem with task definition: " . $task ;
        $response->objects = $inputObj->tasks;
        if (!isset($inputObj->user)){
            $response->user = $inputObj->user;
        }
        json_encode($response);
        return;
      }
      array_push($tasks, $task);
    };


    $response = new Response();

    // Handle the tasks
    foreach ($tasks AS $task)
    {
        if (!isset($task->action)){ continue; }

        $task->type = ucfirst(TypeSanitizer::sanitizeField( $task->type, 'name'));
        $class = "objects\\" . ucfirst($task->type);
        $task->map = $class::getSanitizeMap();

        if (is_array($task->where))
        {
            foreach($task->where AS $tv)
            {
                $wh = Task::Where();
                $wh->column   = TypeSanitizer::sanitizeField( $tv->column, 'name');

                if (is_string($tv->value)) {
                    $wh->value = TypeSanitizer::sanitizeField($tv->value, 'string');
                } elseif (is_array($tv->value)) {
                    $sanitizedArray = array();
                    foreach ($tv->value as $key => $value) {
                        $sanitizedValue = TypeSanitizer::sanitizeField($value, 'string');
                        $sanitizedArray[$key] = $sanitizedValue;
                    }
                    $wh->value = $sanitizedArray;
                }

                if (!isset($tv->operator) || $tv->operator == ""){
                    $wh->operator = "=";
                } else {
                    $wh->operator = TypeSanitizer::sanitizeField( $tv->operator, 'operator');
                }
                if (isset($tv->value2) && $tv->value2 != "" && strtoupper( $wh->operator) == "BETWEEN"){
                    $wh->value2 = TypeSanitizer::sanitizeField( $tv->value2, 'string');
                } 
                $tv = $wh;
            };
        };

        switch ($task->action) {
            // Select read
            case 1:
                $rowData = DB::getRows($task);
                if ($rowData == false){ break; };
                $task->results = $rowData;
                $task->Simplify();
                break;

                // Write new entity
            case 3:
                $newObjects = [];
                $sanitizedObjects = [];
                foreach ($task->objects AS $getObj){
                    $newObj = getTypeByName($task->type);
                    // prepare to store into db
                    if ($getObj->id == null){ $getObj->id = '';};
                    $objNn = TypeSanitizer::rebuildAndSanitizeObjectFromStd($newObj, $getObj);
                    $objNn->user = $inputObj->user;
                    array_push($sanitizedObjects, $objNn);
                }
                foreach ($sanitizedObjects AS $objectToWrite)
                {
                    if (strtolower( $task->type) == 'section'){
                        if ($objectToWrite->color == null || $objectToWrite->color == "")
                        {
                            $objectToWrite->color = ContentGenerator::generatePastelColor();
                        }
                    }
                    $transid = null;
                    // return object back with new item
                    // need to set temporary trans id for api
                    if (isset($objectToWrite->trans_id)){
                        $transid = $objectToWrrite->trans_id;
                        unset($objectToWrite->trans_id);
                    };
                    $result  = DB::writeObject($objectToWrite);
                    if (is_string($result)){
                        $response->status = 1;
                        $response->message = $result;
                    } else {
                        $tempObj = $result;
                        if ($transid != null){
                            $tempObj->trans_id = $transid;
                        }
                        array_push( $newObjects , $tempObj);
                    }
                };
                $task->results = $newObjects;
                break;

             // 5 - update entry
             case 5:
                $newObjects = [];
                $sanitizedObjects = [];
                foreach ($task->objects AS $getObj){
                    $newObj = getTypeByName($task->type);
                    // prepare to store into db
                    $objNn = TypeSanitizer::rebuildAndSanitizeObjectFromStd($newObj, $getObj);
                    $objNn->user = $inputObj->user;
                    array_push($sanitizedObjects, $objNn);
                }
                foreach ($sanitizedObjects AS $objectToWrite)
                {
                    if (strtolower( $task->type) == 'section'){
                        if ($objectToWrite->color == null || $objectToWrite->color == "")
                        {
                            $objectToWrite->color = ContentGenerator::generatePastelColor();
                        }
                    }
                    $transid = null;
                    // return object back with new item
                    // need to set temporary trans id for api
                    if (isset($objectToWrite->trans_id)){
                        $transid = $objectToWrrite->trans_id;
                        unset($objectToWrite->trans_id);
                    };
                    $result  = DB::updateObject($objectToWrite, $globalUser);
                    if (is_string($result)){
                        $response->status = 1;
                        $response->message = $result;
                    } else {
                        $tempObj = $result;
                        if ($transid != null){
                            $tempObj->trans_id = $transid;
                        }
                        array_push( $newObjects , $tempObj);
                    }
                };
                $task->results = $newObjects;
                break;

             // 6 - update `ordered`
             case 6:
                $newObjects = [];
                $sanitizedObjects = [];
                foreach ($task->objects AS $getObj){
                    $newObj = getTypeByName($task->type);
                    // prepare to store into db
                    $objNn = TypeSanitizer::rebuildAndSanitizeObjectFromStd($newObj, $getObj);
                    $objNn->user = $inputObj->user;
                    array_push($sanitizedObjects, $objNn);
                }
                foreach ($sanitizedObjects AS $objectToWrite)
                {
                    $result  = DB::updateObjectOrder($objectToWrite, $globalUser);
                    if (is_string($result)){
                        $response->status = 1;
                        $response->message = $result;
                    } else {
                        $tempObj = $result;
                        array_push( $newObjects , $tempObj);
                    }
                };
                $task->results = $newObjects;
                break;

            // 7 - update single column value
            case 7:
             $newObjects = [];
            $sanitizedObjects = [];
            $ok = false;
            foreach ($task->objects[0] AS $key => $value){
                $newObj = getTypeByName($task->type);
                $typeOfValue = $newObj->sanitizeMap();
                if ($typeOfValue[$key] == null){ break; };
                $sanvalue = TypeSanitizer::sanitizeField($value, $typeOfValue[$key]);
                $sanitizedObjects[0][$key] = $sanvalue;
            }
            $task->objects[0] = $sanitizedObjects;
            if (count($sanitizedObjects) == 0){
                break;
            }
            $result  = DB::updateByParams($task, $globalUser);
            if (is_string($result)){
                $response->status = 1;
                $response->message = $result;
            } else {
                array_push( $newObjects , $result);
            }
            $task->results = $newObjects;
            break;

             // 10 - delete rows
             case 10:
                $newObjects = [];
                $sanitizedObjects = [];
                foreach ($task->objects AS $getObj){
                    $newObj = getTypeByName($task->type);
                    // prepare to store into db
                    $objNn = TypeSanitizer::rebuildAndSanitizeObjectFromStd($newObj, $getObj);
                    $objNn->user = $inputObj->user;
                    array_push($sanitizedObjects, $objNn);
                }
                foreach ($sanitizedObjects AS $objectToWrite)
                {

                    $result  = DB::deleteObject($objectToWrite, $globalUser);
                    if (is_string($result)){
                        $response->status = 1;
                        $response->message = $result;
                    } else {
                        $tempObj = $result;
                        array_push( $newObjects , $tempObj);
                    }
                };
                // $rowData = DB::getRows($task);
                // if ($rowData == false){ break; };
                $task->results = $newObjects;
                //$task->Simplify();
                break;

                // 11 parametric Delete
                             // 10 - delete rows
             case 11:

                $sanitizedObjects = [];
                $task->objects = [];
                $newObj = getTypeByName($task->type);
                // prepare to store into db
                
                $newObj->user = $inputObj->user;
                array_push($sanitizedObjects, $newObj);
                
                $task->objects = $sanitizedObjects;
                $results  = DB::deleteByParams($task, $globalUser);
                    if (is_string($results)){
                        $response->status = 1;
                        $response->message = $result;
                        $task->results = [];
                    } else {
                        $task->results = $results;
                    }

                //$task->Simplify();
                break;

            // 20 - count rows
            case 20:
                $rowData = DB::countRows($task);
                if ($rowData == false){ break; };
                $task->results = [$rowData];
                $task->Simplify();
                break;
            break;
        }
        array_push( $response->results, $task);
        
    }

    echo json_encode($response);
    return;
    
?>