/**
 * 🧪 SCRIPT DE DEBUG STEP 5 - À executer dans la CONSOLE (F12) sur Step 5
 * 
 * Copie-colle ce code entier dans la console et appuie sur Entrée
 * Cela testera immédiatement pourquoi le real-time ne fonctionne pas
 */

console.clear();
console.log('%c🔍 TEST STEP 5 REAL-TIME DEBUG', 'background: blue; color: white; padding: 10px; font-size: 16px; font-weight: bold;');
console.log('');

// TEST 1: Vérification des éléments HTML
console.log('%c TEST 1: VÉRIFICATION HTML', 'background: #4CAF50; color: white; padding: 5px; font-weight: bold;');
console.log('');

const inputs = document.querySelectorAll('.bng-note-input');
console.log(`✅ Inputs trouvés: ${inputs.length}`);

if (inputs.length > 0) {
    const firstInput = inputs[0];
    console.log(`
✅ Premier input:
   - Classe: ${firstInput.className}
   - data-student: ${firstInput.dataset.student}
   - data-subject: ${firstInput.dataset.subject}
   - Valeur: "${firstInput.value}"
`);
}

// Test des IDs de stats
console.log('✅ Éléments de stats:');
const statIds = ['statAvg', 'statPct', 'statMax', 'statMin', 'staticPassing'];
statIds.forEach(id => {
    const el = document.getElementById(id);
    console.log(`   - #${id}: ${el ? '✅ TROUVÉ' : '❌ MANQUANT'}`);
});

// TEST 2: Configuration
console.log('');
console.log('%c TEST 2: CONFIGURATION', 'background: #4CAF50; color: white; padding: 5px; font-weight: bold;');
console.log('');

if (window.__bulletinNotesConfig) {
    console.log('✅ window.__bulletinNotesConfig EXISTS:');
    console.log(window.__bulletinNotesConfig);
} else {
    console.error('❌ window.__bulletinNotesConfig NOT FOUND!');
}

// TEST 3: Test d'une saisie
console.log('');
console.log('%c TEST 3: SIMULATION D\'UNE SAISIE', 'background: #4CAF50; color: white; padding: 5px; font-weight: bold;');
console.log('');

if (inputs.length > 0) {
    const testInput = inputs[0];
    const studentId = testInput.dataset.student;
    const subjectId = testInput.dataset.subject;
    
    console.log(`Simulation sur élève: ${studentId}`);
    console.log(`Simulation sur matière: ${subjectId}`);
    console.log('');
    
    // Change la valeur
    console.log('📝 Changing value to 15...');
    testInput.value = '15';
    
    // Déclenche l'événement input
    const event = new Event('input', { bubbles: true });
    testInput.dispatchEvent(event);
    
    console.log('✅ Événement "input" déclenché');
    console.log('');
    console.log('🔍 Vérifiez dans la console ci-dessus:');
    console.log('   - Vous devriez voir le message "🖊️ Input change"');
    console.log('   - Après ~300ms, vous devriez voir "💾 SAVE TRIGGERED"');
    console.log('   - Puis "📤 SENDING SAVE REQUEST"');
    console.log('');
} else {
    console.error('❌ Aucun input trouvé!');
}

// TEST 4: Vérification de CSRF
console.log('%c TEST 4: VÉRIFICATION CSRF', 'background: #4CAF50; color: white; padding: 5px; font-weight: bold;');
console.log('');

const csrfToken = document.querySelector('meta[name=csrf-token]');
if (csrfToken) {
    console.log(`✅ CSRF Token trouvé: ${csrfToken.content.substring(0, 20)}...`);
} else {
    console.error('❌ Meta CSRF Token NOT FOUND!');
}

console.log('');
console.log('%c✅ DEBUG COMPLET', 'background: blue; color: white; padding: 10px; font-size: 14px; font-weight: bold;');
console.log('');
console.log('Attendez 2-3 secondes et vérifiez si la note s\'est mise à jour.');
console.log('Si pas de mise à jour, attendez le message "💾 SAVE TRIGGERED"');
console.log('Si pas de message, allez à l\'onglet "Network" (F12) pour voir les requêtes.');
