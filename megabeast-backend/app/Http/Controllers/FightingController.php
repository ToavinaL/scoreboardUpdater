<?php

namespace App\Http\Controllers;

use App\Models\Fighting;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FightingController extends Controller
{
    /**
     * Display a listing of all fights.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $fights = Fighting::with(['tournament'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $fights,
                'message' => 'Fights retrieved successfully',
                'total' => $fights->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fights: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new fight.
     * 
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'message' => 'Use POST /fightings to create a new fight'
        ]);
    }

    /**
     * Store a newly created fight in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tournament_id' => 'required|exists:tournaments,id',
                'player1_id' => 'required|exists:players,id|different:player2_id',
                'player2_id' => 'required|exists:players,id|different:player1_id',
                'target_score' => 'required|integer|min:1',
                'status' => 'nullable|in:pending,ongoing,completed'
            ]);

            $validated['score_player1'] = 0;
            $validated['score_player2'] = 0;
            $validated['status'] = $validated['status'] ?? 'pending';

            $fight = Fighting::create($validated);
            $fight->load('tournament');

            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Fight created successfully'
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
                'message' => 'Error creating fight: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified fight.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $fight = Fighting::with(['tournament'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Fight retrieved successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fight: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified fight.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function edit(string $id): JsonResponse
    {
        try {
            $fight = Fighting::with(['tournament'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Use PUT /fightings/{id} to update this fight'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        }
    }

    /**
     * Update the specified fight in storage.
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $fight = Fighting::findOrFail($id);

            $validated = $request->validate([
                'score_player1' => 'sometimes|required|integer|min:0',
                'score_player2' => 'sometimes|required|integer|min:0',
                'status' => 'sometimes|required|in:pending,ongoing,completed',
                'target_score' => 'sometimes|required|integer|min:1'
            ]);

            // Check if fight is completed
            if (isset($validated['score_player1']) || isset($validated['score_player2'])) {
                $p1Score = $validated['score_player1'] ?? $fight->score_player1;
                $p2Score = $validated['score_player2'] ?? $fight->score_player2;
                $targetScore = $validated['target_score'] ?? $fight->target_score;

                if ($p1Score >= $targetScore || $p2Score >= $targetScore) {
                    $validated['status'] = 'completed';
                } else {
                    $validated['status'] = $validated['status'] ?? 'ongoing';
                }
            }

            $fight->update($validated);

            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Fight updated successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
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
                'message' => 'Error updating fight: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified fight from storage.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $fight = Fighting::findOrFail($id);
            $fightId = $fight->id;
            
            $fight->delete();

            return response()->json([
                'success' => true,
                'message' => "Fight {$fightId} deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fight: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increase score for player 1
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function increasePlayer1Score(string $id): JsonResponse
    {
        try {
            $fight = Fighting::findOrFail($id);

            if ($fight->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify completed fight'
                ], 400);
            }

            if ($fight->score_player1 < $fight->target_score) {
                $fight->score_player1++;
                
                // Check if player 1 wins
                if ($fight->score_player1 >= $fight->target_score) {
                    $fight->status = 'completed';
                } else {
                    $fight->status = 'ongoing';
                }
                
                $fight->save();
            }

            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Player 1 score increased'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increase score for player 2
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function increasePlayer2Score(string $id): JsonResponse
    {
        try {
            $fight = Fighting::findOrFail($id);

            if ($fight->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify completed fight'
                ], 400);
            }

            if ($fight->score_player2 < $fight->target_score) {
                $fight->score_player2++;
                
                // Check if player 2 wins
                if ($fight->score_player2 >= $fight->target_score) {
                    $fight->status = 'completed';
                } else {
                    $fight->status = 'ongoing';
                }
                
                $fight->save();
            }

            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Player 2 score increased'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset scores for a fight
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function resetScores(string $id): JsonResponse
    {
        try {
            $fight = Fighting::findOrFail($id);

            $fight->score_player1 = 0;
            $fight->score_player2 = 0;
            $fight->status = 'pending';
            $fight->save();

            return response()->json([
                'success' => true,
                'data' => $fight,
                'message' => 'Fight scores reset successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fight not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fights by tournament
     * 
     * @param string $tournamentId
     * @return JsonResponse
     */
    public function getByTournament(string $tournamentId): JsonResponse
    {
        try {
            $fights = Fighting::where('tournament_id', $tournamentId)
                ->with(['tournament'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $fights,
                'total' => $fights->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
