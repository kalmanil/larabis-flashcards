<?php

namespace App\Features\Flashcards\Http\Controllers;

use App\Features\Flashcards\Models\Deck;
use App\Features\Flashcards\Models\DeckCard;
use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Models\UserCardProgress;
use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearnController
{
    protected function ensureDefaultDeck(): Deck
    {
        $user = Auth::user();
        $deck = $user->decks()->where('is_default', true)->first();

        if (!$deck) {
            $deck = $user->decks()->create([
                'name' => 'My deck',
                'slug' => 'default',
                'is_default' => true,
            ]);
        }

        return $deck;
    }

    public function config(Request $request)
    {
        $deck = $this->ensureDefaultDeck();
        $cardCount = $deck->deckCards()->count();

        if ($cardCount === 0) {
            return redirect()->route('flashcards.dashboard')
                ->with('info', 'Add some cards to your deck first.');
        }

        return TenancyHelper::view('flashcards.learn.config', [
            'cardCount' => $cardCount,
        ]);
    }

    public function startSession(Request $request)
    {
        $request->validate([
            'lang' => 'required|in:ru,en',
            'front_type' => 'required|in:hebrew,transcription,translation,random',
        ]);

        $deck = $this->ensureDefaultDeck();
        $cardIds = $deck->deckCards()->pluck('hebrew_form_id')->shuffle()->values()->all();

        $request->session()->put('learn', [
            'deck_id' => $deck->id,
            'lang' => $request->lang,
            'front_type' => $request->front_type,
            'card_ids' => $cardIds,
            'position' => 0,
            'seen' => [],
        ]);

        return redirect()->route('flashcards.learn.session');
    }

    public function session(Request $request)
    {
        $learn = $request->session()->get('learn');

        if (!$learn || empty($learn['card_ids'])) {
            return redirect()->route('flashcards.learn.config')
                ->with('info', 'Start a new session.');
        }

        $position = $learn['position'] ?? 0;
        $cardIds = $learn['card_ids'];

        if ($position >= count($cardIds)) {
            $request->session()->forget('learn');
            return TenancyHelper::view('flashcards.learn.complete');
        }

        $hebrewForm = HebrewForm::with('translations.language')
            ->find($cardIds[$position]);

        if (!$hebrewForm) {
            $learn['position'] = $position + 1;
            $request->session()->put('learn', $learn);
            return redirect()->route('flashcards.learn.session');
        }

        $sessionTranslations = $hebrewForm->translations
            ->filter(fn ($t) => $t->language->code === $learn['lang']);
        $translations = $sessionTranslations->isNotEmpty()
            ? $sessionTranslations->pluck('text')->implode(', ')
            : $hebrewForm->translations->pluck('text')->implode(', ');
        $transcription = $learn['lang'] === 'ru' ? $hebrewForm->transcription_ru : $hebrewForm->transcription_en;
        $transcription = $transcription ?? $hebrewForm->transcription_ru ?? $hebrewForm->transcription_en ?? '—';

        $frontType = $learn['front_type'];
        if ($frontType === 'random') {
            $frontType = ['hebrew', 'transcription', 'translation'][array_rand(['hebrew', 'transcription', 'translation'])];
        }

        $front = match ($frontType) {
            'hebrew' => $hebrewForm->form_text,
            'transcription' => $transcription,
            'translation' => $translations ?: '—',
            default => $hebrewForm->form_text,
        };

        $reverse = [
            'hebrew' => $hebrewForm->form_text,
            'transcription' => $transcription,
            'translation' => $translations ?: '—',
        ];

        return TenancyHelper::view('flashcards.learn.session', [
            'card' => $hebrewForm,
            'front' => $front,
            'frontType' => $frontType,
            'reverse' => $reverse,
            'position' => $position + 1,
            'total' => count($cardIds),
        ]);
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'known' => 'required|boolean',
        ]);

        $learn = $request->session()->get('learn');
        if (!$learn || empty($learn['card_ids'])) {
            return redirect()->route('flashcards.learn.config');
        }

        $position = $learn['position'] ?? 0;
        if ($position >= count($learn['card_ids'])) {
            $request->session()->forget('learn');
            return redirect()->route('flashcards.learn.config');
        }

        $hebrewFormId = $learn['card_ids'][$position];
        $userId = Auth::id();
        $deckId = $learn['deck_id'];

        UserCardProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'deck_id' => $deckId,
                'hebrew_form_id' => $hebrewFormId,
            ],
            [
                'known' => $request->boolean('known'),
                'last_seen_at' => now(),
            ]
        );

        $learn['position'] = $position + 1;
        $request->session()->put('learn', $learn);

        if ($learn['position'] >= count($learn['card_ids'])) {
            $request->session()->forget('learn');
            return redirect()->route('flashcards.learn.config')->with('success', 'Session complete!');
        }

        return redirect()->route('flashcards.learn.session');
    }

    public function reset(Request $request)
    {
        UserCardProgress::where('user_id', Auth::id())->update(['known' => false]);

        return redirect()->route('flashcards.dashboard')->with('success', 'Progress reset.');
    }
}
