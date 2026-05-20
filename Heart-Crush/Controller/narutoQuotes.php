<?php
header('Content-Type: application/json; charset=utf-8');


// NORMAL PAUSE QUOTES
$normalQuotes = [
    // Naruto
    ["quote" => "When you feel like giving up, remember why you held on for so long.", "character" => "Naruto Uzumaki"],
    ["quote" => "If you don’t like the hand that fate’s dealt you, fight for a new one.", "character" => "Naruto Uzumaki"],
    ["quote" => "The true measure of a shinobi is not how he lives, but how he dies.", "character" => "Naruto Uzumaki"],

    // Hinata
    ["quote" => "It’s because we help each other that we’re able to survive.", "character" => "Hinata Hyuga"],
    ["quote" => "Failure is not falling down but refusing to get back up.", "character" => "Hinata Hyuga"],

    // Shikamaru
    ["quote" => "The hardest battles are fought in your own mind.", "character" => "Shikamaru Nara"],
    ["quote" => "Sometimes, you must stand even when standing is hard.", "character" => "Shikamaru Nara"],

    // Rock Lee
    ["quote" => "A genius who works hard can surpass a genius who doesn’t.", "character" => "Rock Lee"],
    ["quote" => "My motto is to be stronger than yesterday. If I have to, I’ll be stronger than half an hour ago!", "character" => "Rock Lee"],

    // Gaara
    ["quote" => "If love is just a word, then why does it hurt so much when you realize it's not there?", "character" => "Gaara"],
    ["quote" => "It's never too late to start anew.", "character" => "Gaara"],

    // Neji 
    ["quote" => "Fear is not evil. It tells you what your weakness is. Once you know your weakness, you can become stronger.", "character" => "Neji Hyuga"],

    // Tsunade
    ["quote" => "You must believe in yourself. Even when others doubt you.", "character" => "Tsunade"],

    // Minato
    ["quote" => "A parent's love is stronger than any curse.", "character" => "Minato Namikaze"],

    // Jiraiya
    ["quote" => "A place where someone still thinks of you—that’s a place you can call home.", "character" => "Jiraiya"],
    ["quote" => "The true power of shinobi comes from the will to never give up.", "character" => "Jiraiya"],

    // Kakashi
    ["quote" => "To abandon the things you care about… that is the true essence of being human.", "character" => "Kakashi Hatake"],
    ["quote" => "Look underneath the underneath.", "character" => "Kakashi Hatake"],

    // Might Guy
    ["quote" => "A dropout will beat a genius through hard work!", "character" => "Might Guy"]
];



// BOSS FIGHT PAUSE QUOTES

$bossQuotes = [
    // Itachi
    ["quote" => "People live their lives bound by what they accept as correct and true.", "character" => "Itachi Uchiha"],
    ["quote" => "Those who forgive themselves, and are able to accept their true nature… they are the strong ones.", "character" => "Itachi Uchiha"],
    ["quote" => "No matter what you choose, you’ll regret it. That’s life.", "character" => "Itachi Uchiha"],

    // Pain (Nagato)
    ["quote" => "Sometimes you must hurt to know, fall to grow, lose to gain.", "character" => "Pain (Nagato)"],
    ["quote" => "Justice comes from vengeance, but that justice only breeds more vengeance.", "character" => "Pain (Nagato)"],
    ["quote" => "Open your eyes and look within yourself. The answer lies there, and always has.", "character" => "Pain (Nagato)"],

    // Madara
    ["quote" => "Wake up to reality. Nothing ever goes as planned in this world.", "character" => "Madara Uchiha"],
    ["quote" => "Wherever there is light—there are also shadows.", "character" => "Madara Uchiha"],

    // Obito
    ["quote" => "The moment people come to know love, they run the risk of feeling hatred.", "character" => "Obito Uchiha"],
    ["quote" => "No one cared who I was until I stopped caring.", "character" => "Obito Uchiha"],

    // Kakashi
    ["quote" => "To know what is right and choose to ignore it... is the act of a coward.", "character" => "Kakashi Hatake"],

    // Minato
    ["quote" => "A hero always arrives late, but he arrives prepared.", "character" => "Minato Namikaze"],

    // Might Guy
    ["quote" => "A man who cannot forgive himself will never be able to forgive others.", "character" => "Might Guy"],

    // Jiraiya
    ["quote" => "Rejection makes a man stronger.", "character" => "Jiraiya"],

    // Hashirama
    ["quote" => "A shinobi's true worth is revealed when protecting others.", "character" => "Hashirama Senju"],

    // Sasuke
    ["quote" => "When a man learns to love, he must also bear the risk of hatred.", "character" => "Sasuke Uchiha"]
];



// SELECT POOL BASED ON ?type=
$typeParam = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'normal';

$pool = ($typeParam === 'boss') ? $bossQuotes : $normalQuotes;

// Safety: if for some reason pool is empty
if (empty($pool)) {
    echo json_encode([
        "quote"     => "Believe in yourself and keep moving forward.",
        "character" => "Naruto Uzumaki"
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Picks a random quote and output exactly what game.js expects
$choice = $pool[array_rand($pool)];

echo json_encode($choice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
