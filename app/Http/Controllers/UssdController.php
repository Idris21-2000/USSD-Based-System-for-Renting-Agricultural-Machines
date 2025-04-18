<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Machine;

class UssdController extends Controller
{
    public function handle(Request $request)
    {
        $sessionId = $request->input("sessionId");
        $serviceCode = $request->input("serviceCode");
        $phoneNumber = $request->input("phoneNumber");
        $text = $request->input("text");

        $textArray = explode("*", $text);
        $level = count($textArray);

        switch ($level) {
            case 1:
                return response("CON Welcome to AgriRent\n1. Register\n2. Login");
            
            case 2:
                if ($textArray[0] == "1") {
                    return response("CON Enter your full name:");
                } elseif ($textArray[0] == "2") {
                    return response("CON Enter your registered phone number:");
                }
                break;

            case 3:
                if ($textArray[0] == "1") {
                    $name = $textArray[1];
                    return response("CON Enter your location:");
                } elseif ($textArray[0] == "2") {
                    $phone = $textArray[1];
                    $farmer = User::where('phone', $phone)->where('user_type', 'farmer')->first();

                    if ($farmer) {
                        return response("CON Welcome, {$farmer->name}\n1. View Machines\n2. Logout");
                    } else {
                        return response("END Phone number not found. Try again.");
                    }
                }
                break;

            case 4:
                if ($textArray[0] == "1") {
                    // Finish registration
                    $name = $textArray[1];
                    $location = $textArray[2];

                    $exists = User::where('phone', $phoneNumber)->first();
                    if ($exists) {
                        return response("END You are already registered.");
                    }

                    User::create([
                        'name' => $name,
                        'location' => $location,
                        'phone' => $phoneNumber,
                        'user_type' => 'farmer',
                        'password' => bcrypt('default123') // optional
                    ]);

                    return response("END Registration successful!");
                } elseif ($textArray[0] == "2" && $textArray[2] == "1") {
                    $machines = Machine::where('status', 'available')->get();

                    if ($machines->isEmpty()) {
                        return response("END No machines available currently.");
                    }

                    $machineList = "";
                    foreach ($machines as $index => $machine) {
                        $machineList .= ($index + 1) . ". " . $machine->name . "\n";
                    }

                    return response("END Available Machines:\n" . $machineList);
                }
                break;

            default:
                return response("END Invalid input.");
        }
    }
}

