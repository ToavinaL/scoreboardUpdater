<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TournamentController extends Controller
{
    /**
     * Display a listing of all tournaments.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $tournaments = Tournament::with(['fightings'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $tournaments,
                'message' => 'Tournaments retrieved successfully',
                'total' => $tournaments->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tournaments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new tournament.
     * 
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'message' => 'Use POST /tournaments to create a new tournament'
        ]);
    }

    /**
     * Store a newly created tournament in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:tournaments|max:255',
                'game' => 'required|string|max:255',
                'format' => 'required|string|max:255',
                'date' => 'required|date'
            ]);

            $tournament = Tournament::create($validated);

            return response()->json([
                'success' => true,
                'data' => $tournament,
                'message' => 'Tournament created successfully'
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
                'message' => 'Error creating tournament: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tournament.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $tournament = Tournament::with(['fightings'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $tournament,
                'message' => 'Tournament retrieved successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tournament: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified tournament.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function edit(string $id): JsonResponse
    {
        try {
            $tournament = Tournament::with(['fightings'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $tournament,
                'message' => 'Use PUT /tournaments/{id} to update this tournament'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
            ], 404);
        }
    }

    /**
     * Update the specified tournament in storage.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|unique:tournaments,name,' . $id . '|max:255',
                'game' => 'sometimes|required|string|max:255',
                'format' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date'
            ]);

            $tournament->update($validated);

            return response()->json([
                'success' => true,
                'data' => $tournament,
                'message' => 'Tournament updated successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
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
                'message' => 'Error updating tournament: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tournament from storage.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $tournament = Tournament::findOrFail($id);
            $tournamentName = $tournament->name;
            
            $tournament->delete();

            return response()->json([
                'success' => true,
                'message' => "Tournament '{$tournamentName}' deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tournament: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tournament statistics
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function getStats(string $id): JsonResponse
    {
        try {
            $tournament = Tournament::with(['fightings'])->findOrFail($id);
            $fightings = $tournament->fightings;

            $totalFights = $fightings->count();
            $completedFights = $fightings->where('status', 'completed')->count();
            $ongoingFights = $fightings->where('status', 'ongoing')->count();
            $pendingFights = $fightings->where('status', 'pending')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament,
                    'statistics' => [
                        'total_fights' => $totalFights,
                        'completed_fights' => $completedFights,
                        'ongoing_fights' => $ongoingFights,
                        'pending_fights' => $pendingFights,
                        'completion_rate' => $totalFights > 0 ? round(($completedFights / $totalFights) * 100, 2) . '%' : '0%'
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tournament leaderboard
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function getLeaderboard(string $id): JsonResponse
    {
        try {
            $tournament = Tournament::with(['fightings'])->findOrFail($id);
            $fightings = $tournament->fightings;

            // Calculate player stats
            $playerStats = [];
            
            foreach ($fightings as $fight) {
                if (!isset($playerStats[$fight->player1_id])) {
                    $playerStats[$fight->player1_id] = [
                        'player_id' => $fight->player1_id,
                        'wins' => 0,
                        'losses' => 0,
                        'total_fights' => 0
                    ];
                }
                if (!isset($playerStats[$fight->player2_id])) {
                    $playerStats[$fight->player2_id] = [
                        'player_id' => $fight->player2_id,
                        'wins' => 0,
                        'losses' => 0,
                        'total_fights' => 0
                    ];
                }

                if ($fight->status === 'completed') {
                    if ($fight->score_player1 > $fight->score_player2) {
                        $playerStats[$fight->player1_id]['wins']++;
                        $playerStats[$fight->player2_id]['losses']++;
                    } else {
                        $playerStats[$fight->player2_id]['wins']++;
                        $playerStats[$fight->player1_id]['losses']++;
                    }
                }

                $playerStats[$fight->player1_id]['total_fights']++;
                $playerStats[$fight->player2_id]['total_fights']++;
            }

            // Sort by wins
            usort($playerStats, function ($a, $b) {
                return $b['wins'] <=> $a['wins'];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'tournament' => $tournament,
                    'leaderboard' => $playerStats
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
