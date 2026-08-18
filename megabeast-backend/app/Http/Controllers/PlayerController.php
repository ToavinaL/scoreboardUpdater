<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    /**
     * Display a listing of all players.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $players = Player::all();
            return response()->json([
                'success' => true,
                'data' => $players,
                'message' => 'Players retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving players: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new player.
     * 
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'message' => 'Use POST /players to create a new player'
        ]);
    }

    /**
     * Store a newly created player in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nickname' => 'required|string|unique:players|max:255',
                'real_name' => 'required|string|max:255',
                'country' => 'nullable|string|max:255',
                'team' => 'nullable|string|max:255',
                'avatar' => 'nullable|string|max:500'
            ]);

            $player = Player::create($validated);

            return response()->json([
                'success' => true,
                'data' => $player,
                'message' => 'Player created successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating player: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified player.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $player,
                'message' => 'Player retrieved successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving player: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified player.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function edit(string $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $player,
                'message' => 'Use PUT /players/{id} to update this player'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found'
            ], 404);
        }
    }

    /**
     * Update the specified player in storage.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);

            $validated = $request->validate([
                'nickname' => 'sometimes|required|string|unique:players,nickname,' . $id . '|max:255',
                'real_name' => 'sometimes|required|string|max:255',
                'country' => 'nullable|string|max:255',
                'team' => 'nullable|string|max:255',
                'avatar' => 'nullable|string|max:500'
            ]);

            $player->update($validated);

            return response()->json([
                'success' => true,
                'data' => $player,
                'message' => 'Player updated successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating player: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified player from storage.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);
            $playerName = $player->nickname;
            
            $player->delete();

            return response()->json([
                'success' => true,
                'message' => "Player '{$playerName}' deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting player: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get player statistics
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function getStats(string $id): JsonResponse
    {
        try {
            $player = Player::findOrFail($id);
            $fights = $player->fighting()->get();
            
            $totalFights = $fights->count();
            $wins = $fights->where('status', 'completed')->count();
            $winRate = $totalFights > 0 ? ($wins / $totalFights) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'player' => $player,
                    'total_fights' => $totalFights,
                    'wins' => $wins,
                    'win_rate' => round($winRate, 2) . '%'
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found'
            ], 404);
        }
    }
}
