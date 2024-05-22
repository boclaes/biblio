<?php

namespace App\Http\Controllers\MQTT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RaspberryPi;
use App\Models\Book;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;

class MqttController extends Controller
{
    public function findRaspberryPi()
    {
        Log::info('Initiating network scan to find Raspberry Pi.');
        $output = [];
        $retval = null;
        exec(base_path('scripts/scan_network.bat'), $output, $retval);
        if ($retval != 0) {
            Log::error('Network scan failed.', ['return_value' => $retval]);
            return back()->with('error', 'Failed to scan the network.');
        }
    
        Log::info('Network scan completed.', ['output' => $output]);
    
        foreach ($output as $line) {
            Log::debug('Analyzing line from network scan output.', ['line' => $line]);
            if (strpos($line, 'b8-27-eb') !== false) {
                preg_match('/\d{1,3}(\.\d{1,3}){3}/', $line, $matches);
                if (!empty($matches)) {
                    Log::info('Found Raspberry Pi IP address.', ['ip' => $matches[0]]);
                    return $matches[0];
                }
            }
        }
    
        Log::warning('No Raspberry Pi found on the network.');
        return back()->with('error', 'No Raspberry Pi found on the network.');
    }


    public function registerRPI()
    {
        $user = Auth::user();  // Get the authenticated user
        $userId = $user->id;  // Getting the logged-in user's ID
    
        Log::info('Attempting to register Raspberry Pi', ['user_id' => $userId]);
    
        // Check if user already has a Raspberry Pi registered
        if (RaspberryPi::where('user_id', $userId)->exists()) {
            Log::warning('User already has a Raspberry Pi registered', ['user_id' => $userId]);
            return back()->with('error', 'You already have a Raspberry Pi registered.');
        }
    
        // Delete existing tokens
        $user->tokens()->delete();
        Log::info('Deleted existing tokens', ['user_id' => $userId]);
    
        // Generate a new API token
        $token = $user->createToken('api-token')->plainTextToken;
        Log::info('Generated new API token', ['user_id' => $userId]);
    
        try {
            $mqtt = new MqttClient('192.168.1.198', 1883, 'laravel_publisher'); // Use dynamically found IP address
            $mqtt->connect();
            $mqtt->publish("/rpi/register", json_encode(['user_id' => $userId, 'api_token' => $token, 'ip_address' => '192.168.1.198']), 0);
            $mqtt->disconnect();
            Log::info('MQTT registration request sent', ['user_id' => $userId]);
        } catch (MqttClientException $e) {
            Log::error('Failed to send registration request via MQTT', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send registration request: ' . $e->getMessage());
        }
    
        return back()->with('status', 'Registration request sent!');
    }    

    public function apiRegisterRPI(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'location_id' => 'required|integer',
            'unique_identifier' => 'required|string',
            'ip_address' => 'required|string'
        ]);
    
        Log::info('API request to register Raspberry Pi', $request->all());
    
        // Check if user already has a Raspberry Pi registered
        if (RaspberryPi::where('user_id', $request->user_id)->exists()) {
            Log::warning('User attempted to register a Raspberry Pi but one is already registered', ['user_id' => $request->user_id]);
            return response()->json(['error' => 'User already has a Raspberry Pi registered.'], 400);
        }
    
        // Register the Raspberry Pi
        RaspberryPi::create([
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'unique_identifier' => $request->unique_identifier,
            'ip_address' => $request->ip_address
        ]);
    
        Log::info('Raspberry Pi registered successfully', ['user_id' => $request->user_id]);
        return response()->json(['message' => 'RPI registered successfully']);
    }    

    public function showBook(Request $request, $id)
    {
        $user = Auth::user();
        $book = Book::findOrFail($id);
        $rpi = RaspberryPi::where('user_id', $user->id)->first();
    
        if (!$rpi) {
            return back()->with('error', 'No Raspberry Pi registered for this user.');
        }
    
        try {
            $mqtt = new MqttClient($rpi->ip_address, 1883, 'laravel_publisher'); // Use the IP from the database
            $mqtt->connect();
            $mqtt->publish("/rpi/commands/{$rpi->unique_identifier}", json_encode(['place' => $book->place]), 0);
            $mqtt->disconnect();
        } catch (MqttClientException $e) {
            return back()->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    
        return back()->with('status', 'Show book request sent!');
    }    
}
