<?php

namespace App\Http\Controllers\MQTT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RaspberryPi;
use App\Models\Book;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;

class MqttController extends Controller
{
    public function registerRPI()
    {
        $user = Auth::user();  // Get the authenticated user
        $userId = $user->id;  // Getting the logged-in user's ID

        // Check if user already has a Raspberry Pi registered
        if (RaspberryPi::where('user_id', $userId)->exists()) {
            return back()->with('error', 'You already have a Raspberry Pi registered.');
        }

        // Delete existing tokens
        $user->tokens()->delete();

        // Generate a new API token
        $token = $user->createToken('api-token')->plainTextToken;

        try {
            $mqtt = new MqttClient('192.168.1.198', 1883, 'laravel_publisher'); // Update with your broker's address
            $mqtt->connect();
            $mqtt->publish("/rpi/register", json_encode(['user_id' => $userId, 'api_token' => $token]), 0);
            $mqtt->disconnect();
        } catch (MqttClientException $e) {
            return back()->with('error', 'Failed to send registration request: ' . $e->getMessage());
        }

        return back()->with('status', 'Registration request sent!');
    }

    public function apiRegisterRPI(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'location_id' => 'required|integer',
            'unique_identifier' => 'required|string'
        ]);

        // Check if user already has a Raspberry Pi registered
        if (RaspberryPi::where('user_id', $request->user_id)->exists()) {
            return response()->json(['error' => 'User already has a Raspberry Pi registered.'], 400);
        }

        // Assuming you have a model setup for RaspberryPi
        RaspberryPi::create([
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'unique_identifier' => $request->unique_identifier
        ]);

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
            $mqtt = new MqttClient('192.168.1.198', 1883, 'laravel_publisher'); // Update with your broker's address
            $mqtt->connect();
            $mqtt->publish("/rpi/commands/{$rpi->unique_identifier}", json_encode(['place' => $book->place]), 0);
            $mqtt->disconnect();
        } catch (MqttClientException $e) {
            return back()->with('error', 'Failed to send message: ' . $e->getMessage());
        }

        return back()->with('status', 'Show book request sent!');
    }
}
