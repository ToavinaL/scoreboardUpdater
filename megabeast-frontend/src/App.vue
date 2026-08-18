<script setup lang="ts">
import { ref, computed } from 'vue'

interface Player {
  id: number
  name: string
  score: number
  status: 'active' | 'victory' | 'defeat'
}

const players = ref<Player[]>([
  { id: 1, name: 'Toavina', score: 0, status: 'active' },
  { id: 2, name: 'Steven', score: 0, status: 'active' }
])

const maxScore = ref<number>(3)

/**
 * Check if a player has finished fighting based on their score
 * @param scorePlayer - The player's current score
 * @returns true if score >= maxScore, false otherwise
 */
function finishFighting(scorePlayer: number): boolean {
  return scorePlayer >= maxScore.value
}

/**
 * Increase the score of a player by name
 * @param playerName - The name of the player to increase score
 */
function increaseScore(playerName: string): void {
  const player = players.value.find(p => p.name === playerName)
  if (player && !finishFighting(player.score)) {
    player.score++
    updatePlayerStatus(player)
  }
}

/**
 * Decrease the score of a player by name
 * @param playerName - The name of the player to decrease score
 */
function decreaseScore(playerName: string): void {
  const player = players.value.find(p => p.name === playerName)
  if (player && player.score > 0) {
    player.score--
    updatePlayerStatus(player)
  }
}

/**
 * Reset score for a specific player
 * @param playerName - The name of the player whose score to reset
 */
function resetScore(playerName: string): void {
  const player = players.value.find(p => p.name === playerName)
  if (player) {
    player.score = 0
    player.status = 'active'
  }
}

/**
 * Reset all players' scores
 */
function resetAllScores(): void {
  players.value.forEach(player => {
    player.score = 0
    player.status = 'active'
  })
}

/**
 * Update player status based on their score
 * @param player - The player to update status
 */
function updatePlayerStatus(player: Player): void {
  if (finishFighting(player.score)) {
    player.status = 'victory'
  } else {
    player.status = 'active'
  }
}

/**
 * Add a new player to the tournament
 * @param name - The name of the new player
 */
function addPlayer(name: string): void {
  const newId = Math.max(...players.value.map(p => p.id), 0) + 1
  players.value.push({ id: newId, name, score: 0, status: 'active' })
}

/**
 * Remove a player from the tournament
 * @param playerId - The ID of the player to remove
 */
function removePlayer(playerId: number): void {
  players.value = players.value.filter(p => p.id !== playerId)
}

/**
 * Get the winner of the tournament (first player to reach max score)
 * @returns The winning player or null if no winner yet
 */
const winner = computed(() => {
  return players.value.find(p => p.status === 'victory') || null
})

/**
 * Check if tournament is ongoing
 * @returns true if any player is still active, false otherwise
 */
const isTournamentOngoing = computed(() => {
  return players.value.some(p => p.status === 'active')
})

const tournamentName: string = 'MEGABEAST Championship'
const game: string = 'Mortal Kombat 1'
const format: string = 'Double Elimination'
const lieu: string = 'Antananarivo'
const playerCount: number = 32

</script>

<template>
  <main>
    <header>
      <h1>{{ tournamentName }}</h1>
      <div class="tournament-info">
        <p><strong>Jeu :</strong> {{ game }}</p>
        <p><strong>Joueurs :</strong> {{ playerCount }}</p>
        <p><strong>Lieu :</strong> {{ lieu }}</p>
        <p><strong>Format :</strong> {{ format }}</p>
      </div>
    </header>

    <div v-if="winner" class="winner-section">
      <h2>🏆 VICTOIRE 🏆</h2>
      <p>{{ winner.name }} remporte le tournoi !</p>
      <button @click="resetAllScores">Nouveau tournoi</button>
    </div>

    <div class="scoreboard">
      <div v-for="player in players" :key="player.id" class="player-card">
        <h3>{{ player.name }}</h3>
        <div class="score-display">
          <span class="score">{{ player.score }}</span>
          <span class="max-score">/ {{ maxScore }}</span>
        </div>
        
        <div class="button-group">
          <button @click="increaseScore(player.name)" class="btn-increase">+</button>
          <button @click="decreaseScore(player.name)" class="btn-decrease">-</button>
          <button @click="resetScore(player.name)" class="btn-reset">Reset</button>
          <button @click="removePlayer(player.id)" class="btn-remove">Supprimer</button>
        </div>

        <div class="status">
          <p v-if="player.status === 'victory'" class="victory">
            ✓ VICTOIRE !
          </p>
          <p v-else class="active">
            Match en cours...
          </p>
        </div>
      </div>
    </div>

    <footer class="controls">
      <button @click="resetAllScores" class="btn-reset-all">Réinitialiser tous les scores</button>
    </footer>
  </main>
</template>

<style scoped>
main {
  padding: 20px;
  font-family: Arial, sans-serif;
}

header {
  margin-bottom: 30px;
  border-bottom: 2px solid #333;
  padding-bottom: 15px;
}

h1 {
  font-size: 2.5em;
  color: #d4af37;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  margin: 0 0 15px 0;
}

.tournament-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
}

.tournament-info p {
  margin: 5px 0;
}

.winner-section {
  background-color: #ffd700;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  margin-bottom: 30px;
  animation: pulse 1s infinite;
}

.winner-section h2 {
  font-size: 2em;
  margin: 10px 0;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.scoreboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.player-card {
  border: 3px solid #333;
  border-radius: 10px;
  padding: 20px;
  background-color: #f5f5f5;
  transition: transform 0.2s, box-shadow 0.2s;
}

.player-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.player-card h3 {
  font-size: 1.5em;
  margin: 0 0 15px 0;
  color: #333;
}

.score-display {
  font-size: 2em;
  margin-bottom: 15px;
  text-align: center;
}

.score {
  font-weight: bold;
  color: #d4af37;
  font-size: 1.5em;
}

.max-score {
  color: #666;
}

.button-group {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

button {
  padding: 8px 12px;
  font-size: 1em;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s;
  flex: 1;
  min-width: 60px;
}

.btn-increase {
  background-color: #4caf50;
  color: white;
}

.btn-increase:hover {
  background-color: #45a049;
}

.btn-decrease {
  background-color: #ff9800;
  color: white;
}

.btn-decrease:hover {
  background-color: #e68900;
}

.btn-reset {
  background-color: #2196f3;
  color: white;
}

.btn-reset:hover {
  background-color: #0b7dda;
}

.btn-remove {
  background-color: #f44336;
  color: white;
}

.btn-remove:hover {
  background-color: #da190b;
}

.btn-reset-all {
  background-color: #9c27b0;
  color: white;
  padding: 10px 20px;
  font-size: 1.1em;
}

.btn-reset-all:hover {
  background-color: #7b1fa2;
}

.status {
  text-align: center;
  margin-top: 10px;
}

.victory {
  color: #4caf50;
  font-weight: bold;
  font-size: 1.2em;
}

.active {
  color: #ff9800;
  font-size: 1em;
}

.controls {
  text-align: center;
  padding-top: 20px;
  border-top: 2px solid #333;
}
</style>