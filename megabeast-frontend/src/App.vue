<script setup lang="ts">
import { ref, computed } from 'vue'

interface Player {
  id: number
  name: string
}

interface Fighting {
  id: number
  player1Id: number
  player2Id: number
  scorePlayer1: number
  scorePlayer2: number
  winnerId: number | null
  status: 'pending' | 'ongoing' | 'finished'
}

const fighting = ref<Fighting>({
  id: 1,
  player1Id: 1,
  player2Id: 2,
  scorePlayer1: 0,
  scorePlayer2: 0,
  winnerId: null,
  status: 'pending'
})

const players = ref<Player[]>([
  { id: 1, name: 'Toavina' },
  { id: 2, name: 'Steven' }
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

function getPlayerById(playerId: number): Player | undefined {
  return players.value.find(player => player.id === playerId)
}

/**
 * Add a new player to the tournament
 * @param name - The name of the new player
 */
function addPlayer(name: string): void {
  const newId = Math.max(...players.value.map(p => p.id), 0) + 1
  players.value.push({ id: newId, name })
}

/**
 * Remove a player from the tournament
 * @param playerId - The ID of the player to remove
 */
function removePlayer(playerId: number): void {
  players.value = players.value.filter(p => p.id !== playerId)
}

/**
 * Increase fighting score for a player
 * Manages the state cycle: pending → ongoing → finished
 * @param playerId - The ID of the player whose score to increase
 */
function increaseFightingScore(playerId: number): void {
  // Determine which player to modify
  if (playerId === fighting.value.player1Id) {
    // Modify scorePlayer1
    if (fighting.value.scorePlayer1 < maxScore.value) {
      fighting.value.scorePlayer1++

      // Check if player1 has reached max score
      if (fighting.value.scorePlayer1 >= maxScore.value) {
        fighting.value.winnerId = playerId
        fighting.value.status = 'finished'
      } else {
        fighting.value.status = 'ongoing'
      }
    }
  } else if (playerId === fighting.value.player2Id) {
    // Modify scorePlayer2
    if (fighting.value.scorePlayer2 < maxScore.value) {
      fighting.value.scorePlayer2++

      // Check if player2 has reached max score
      if (fighting.value.scorePlayer2 >= maxScore.value) {
        fighting.value.winnerId = playerId
        fighting.value.status = 'finished'
      } else {
        fighting.value.status = 'ongoing'
      }
    }
  }
}

/**
 * Decrease fighting score for a player
 * @param playerId - The ID of the player whose score to decrease
 */
function decreaseFightingScore(playerId: number): void {
  if (playerId === fighting.value.player1Id) {
    if (fighting.value.scorePlayer1 > 0) {
      fighting.value.scorePlayer1--
      // Reset status to ongoing if it was finished
      if (fighting.value.status === 'finished') {
        fighting.value.winnerId = null
        fighting.value.status = 'ongoing'
      }
    }
  } else if (playerId === fighting.value.player2Id) {
    if (fighting.value.scorePlayer2 > 0) {
      fighting.value.scorePlayer2--
      // Reset status to ongoing if it was finished
      if (fighting.value.status === 'finished') {
        fighting.value.winnerId = null
        fighting.value.status = 'ongoing'
      }
    }
  }
}

/**
 * Reset the fighting state to pending with scores at 0
 */
function resetFighting(): void {
  fighting.value.scorePlayer1 = 0
  fighting.value.scorePlayer2 = 0
  fighting.value.winnerId = null
  fighting.value.status = 'pending'
}

/**
 * Get the winner of the current fight
 * @returns The winning player or null if fight is not finished
 */
const fightWinner = computed(() => {
  if (fighting.value.status === 'finished' && fighting.value.winnerId) {
    return getPlayerById(fighting.value.winnerId) || null
  }
  return null
})

/**
 * Check if fight is ongoing
 * @returns true if fight is in progress, false otherwise
 */
const isFightOngoing = computed(() => {
  return fighting.value.status === 'ongoing'
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

    <div v-if="fightWinner" class="winner-section">
      <h2>🏆 VICTOIRE 🏆</h2>
      <p>{{ fightWinner.name }} gagne ce combat !</p>
      <button @click="resetFighting">Prochain combat</button>
    </div>

    <div class="fighting-section">
      <FightingScoreboard />
      <div class="fighting-container">
        <div class="fighter fighter-1">
          <h3>{{ getPlayerById(fighting.player1Id)?.name }}</h3>
          <div class="fighting-score">{{ fighting.scorePlayer1 }}</div>
          <div class="fighting-buttons">
            <button @click="increaseFightingScore(fighting.player1Id)" class="btn-score-increase">+</button>
            <button @click="decreaseFightingScore(fighting.player1Id)" class="btn-score-decrease">-</button>
          </div>
        </div>

        <div class="fighting-status">
          <p class="status-badge" :class="fighting.status">{{ fighting.status.toUpperCase() }}</p>
          <p class="max-score-info">Meilleur des {{ maxScore }}</p>
          <div v-if="fighting.status === 'finished' && fighting.winnerId !== null" class="winner-display">
            <p class="winner-text">🏆 {{ getPlayerById(fighting.winnerId)?.name }} gagne ! 🏆</p>
          </div>
          <button v-if="fighting.status === 'finished'" @click="resetFighting" class="btn-next-fight">Prochain combat</button>
        </div>

        <div class="fighter fighter-2">
          <h3>{{ getPlayerById(fighting.player2Id)?.name }}</h3>
          <div class="fighting-score">{{ fighting.scorePlayer2 }}</div>
          <div class="fighting-buttons">
            <button @click="increaseFightingScore(fighting.player2Id)" class="btn-score-increase">+</button>
            <button @click="decreaseFightingScore(fighting.player2Id)" class="btn-score-decrease">-</button>
          </div>
        </div>
      </div>
    </div>
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

.fighting-section {
  margin-top: 40px;
  padding: 30px;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
  border-radius: 15px;
  color: white;
}

.fighting-section h2 {
  text-align: center;
  font-size: 2em;
  margin-bottom: 30px;
  color: #d4af37;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.fighting-container {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  align-items: center;
}

.fighter {
  background-color: rgba(255, 255, 255, 0.1);
  border: 2px solid #d4af37;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  transition: all 0.3s ease;
}

.fighter:hover {
  background-color: rgba(212, 175, 55, 0.1);
  transform: scale(1.05);
}

.fighter h3 {
  font-size: 1.5em;
  margin: 0 0 15px 0;
  color: #d4af37;
}

.fighting-score {
  font-size: 4em;
  font-weight: bold;
  color: #ffd700;
  margin: 20px 0;
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
}

.fighting-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn-score-increase {
  background-color: #4caf50;
  color: white;
  padding: 10px 15px;
  font-size: 1.2em;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-score-increase:hover {
  background-color: #45a049;
  transform: scale(1.1);
}

.btn-score-decrease {
  background-color: #ff9800;
  color: white;
  padding: 10px 15px;
  font-size: 1.2em;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-score-decrease:hover {
  background-color: #e68900;
  transform: scale(1.1);
}

.fighting-status {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 15px;
}

.status-badge {
  font-size: 1.5em;
  font-weight: bold;
  padding: 10px 20px;
  border-radius: 20px;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.status-badge.pending {
  background-color: #ff9800;
  color: white;
}

.status-badge.ongoing {
  background-color: #2196f3;
  color: white;
  animation: blink 1s infinite;
}

.status-badge.finished {
  background-color: #4caf50;
  color: white;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.max-score-info {
  font-size: 1em;
  color: #b0bec5;
  margin: 0;
}

.winner-display {
  background-color: #ffd700;
  color: #000;
  padding: 15px;
  border-radius: 10px;
  margin: 10px 0;
  animation: pulse 0.8s infinite;
}

.winner-text {
  font-size: 1.3em;
  font-weight: bold;
  margin: 0;
  color: #d4af37;
}

.btn-next-fight {
  background-color: #9c27b0;
  color: white;
  padding: 12px 24px;
  font-size: 1em;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-next-fight:hover {
  background-color: #7b1fa2;
  transform: scale(1.05);
}
</style>