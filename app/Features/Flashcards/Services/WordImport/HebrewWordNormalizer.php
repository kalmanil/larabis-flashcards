<?php

namespace App\Features\Flashcards\Services\WordImport;

/**
 * Normalizes raw dictionary data (Wiktionary, etc.) into a clean, consistent JSON schema.
 * Prefers modern Israeli Hebrew usage.
 */
class HebrewWordNormalizer
{
    protected static array $transliterationMap = [
        'š' => 's', 'ś' => 's', 'ṣ' => 's',
        'ē' => 'e', 'é' => 'e', 'ə' => 'e', 'ĕ' => 'e', 'ê' => 'e',
        'ā' => 'a', 'á' => 'a', 'ă' => 'a', 'â' => 'a', 'à' => 'a',
        'ō' => 'o', 'ó' => 'o', 'ô' => 'o', 'ò' => 'o',
        'ū' => 'u', 'ú' => 'u', 'û' => 'u', 'ù' => 'u',
        'ī' => 'i', 'í' => 'i', 'î' => 'i', 'ì' => 'i',
        'ḥ' => 'h', 'ḵ' => 'kh', 'ẖ' => 'h',
        'ʾ' => "'", 'ʿ' => "'", 'ʼ' => "'",
        '·' => '', 'ː' => '', 'ˈ' => '', 'ˌ' => '',
    ];

    protected static array $posMap = [
        'noun' => 'noun',
        'verb' => 'verb',
        'adjective' => 'adjective',
        'adj' => 'adjective',
        'adverb' => 'adverb',
        'adv' => 'adverb',
        'interjection' => 'interjection',
        'interj' => 'interjection',
        'proper noun' => 'proper_noun',
        'proper_noun' => 'proper_noun',
        'name' => 'proper_noun',
    ];

    protected static array $metaLabels = [
        'uncountable', 'countable', 'transitive', 'intransitive',
        'masculine', 'feminine', 'plural', 'singular',
        'conventional greeting', 'traditional jewish greeting',
        'greeting', 'farewell',
    ];

    protected static array $greetingIndicators = [
        'hello', 'hi', 'goodbye', 'bye', 'greeting', 'farewell',
    ];

    protected static array $nameIndicators = [
        'given name', 'name', 'title of god', 'god', 'divine',
    ];

    public function normalize(string $hebrewWord, array $raw): array
    {
        $transliteration = $this->extractTransliterationRu($raw, $hebrewWord);

        $root = $this->normalizeRoot($raw['shoresh_root'] ?? null);

        $partOfSpeech = $this->extractPartOfSpeech($raw);

        $meanings = $this->extractMeanings($raw, $transliteration, $hebrewWord);

        $isGreeting = $this->detectGreeting($raw, $meanings);

        $isName = $this->detectName($raw, $meanings);

        $result = [
            'word' => $hebrewWord,
            'transliteration' => $transliteration,
            'root' => $root,
            'part_of_speech' => $partOfSpeech,
            'meanings' => $meanings,
        ];

        if ($isGreeting) {
            $result['is_greeting'] = true;
        }

        if ($isName) {
            $result['is_name'] = true;
        }

        $related = $this->extractRelated($raw, $root);
        if (!empty($related)) {
            $result['related'] = $related;
        }

        return $this->pruneEmpty($result);
    }

    protected function extractTransliterationRu(array $raw, string $hebrewWord): string
    {
        $fromDesc = $this->extractDescRu($raw);
        if ($fromDesc !== null && $fromDesc !== '') {
            return $this->normalizeRussian($fromDesc);
        }
        $input = $raw['transcription_ru'] ?? null;
        return $this->normalizeTransliterationRu($input, $hebrewWord);
    }

    protected function extractDescRu(array $raw): ?string
    {
        $parsed = $raw['parsed_wikitext'] ?? null;
        if (!$parsed || !isset($parsed['templates_flat'])) {
            return null;
        }
        foreach ($parsed['templates_flat'] as $t) {
            if (($t['name'] ?? '') !== 'desc') {
                continue;
            }
            $params = $t['params'] ?? [];
            $lang = $params['1'] ?? $params[1] ?? null;
            if ($lang !== 'ru') {
                continue;
            }
            $form = $params['2'] ?? $params[2] ?? null;
            if ($form !== null && trim($form) !== '' && preg_match('/[\x{0400}-\x{04FF}]/u', $form)) {
                return trim($form);
            }
        }
        return null;
    }

    protected function normalizeTransliterationRu(?string $input, string $hebrewWord): string
    {
        if ($input !== null && trim($input) !== '') {
            $s = trim($input);
            if (preg_match('/[\x{0400}-\x{04FF}]/u', $s)) {
                return $this->normalizeRussian($s);
            }
            return $this->latinToRussian($s);
        }
        return $this->hebrewToRussian($hebrewWord);
    }

    protected function normalizeRussian(string $s): string
    {
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    protected function latinToRussian(string $latin): string
    {
        foreach (self::$transliterationMap as $from => $to) {
            $latin = str_replace($from, $to, $latin);
        }
        $latin = preg_replace('/[^\p{L}\']/u', '', $latin);
        $map = [
            'sh' => 'ш', 'ch' => 'х', 'kh' => 'х', 'ts' => 'ц', 'zh' => 'ж',
            'a' => 'а', 'b' => 'б', 'v' => 'в', 'g' => 'г', 'd' => 'д', 'e' => 'е',
            'z' => 'з', 'i' => 'и', 'y' => 'й', 'k' => 'к', 'l' => 'л', 'm' => 'м',
            'n' => 'н', 'o' => 'о', 'p' => 'п', 'r' => 'р', 's' => 'с', 't' => 'т',
            'u' => 'у', 'f' => 'ф', 'h' => 'х', 'c' => 'ц', "'" => 'ъ', 'ʻ' => 'ъ',
        ];
        $latin = mb_strtolower($latin);
        $out = '';
        $len = mb_strlen($latin);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($latin, $i, 1);
            $bigram = $i < $len - 1 ? $c . mb_substr($latin, $i + 1, 1) : '';
            if (isset($map[$bigram])) {
                $out .= $map[$bigram];
                $i++;
            } else {
                $out .= $map[$c] ?? $c;
            }
        }
        return $out;
    }

    protected function hebrewToRussian(string $hebrew): string
    {
        $hebrew = preg_replace('/[\x{05B0}\x{05B1}\x{05B2}\x{05B3}\x{05B4}\x{05B5}\x{05B6}\x{05B7}\x{05B8}\x{05B9}\x{05BA}\x{05BB}\x{05BC}\x{05BD}\x{05BF}\x{05C1}\x{05C2}]/u', '', $hebrew);
        $map = [
            'א' => '', 'ב' => 'в', 'ג' => 'г', 'ד' => 'д', 'ה' => 'х', 'ו' => 'в', 'ז' => 'з',
            'ח' => 'х', 'ט' => 'т', 'י' => 'й', 'כ' => 'х', 'ך' => 'х', 'ל' => 'л', 'מ' => 'м',
            'ם' => 'м', 'נ' => 'н', 'ן' => 'н', 'ס' => 'с', 'ע' => '', 'פ' => 'п', 'ף' => 'ф',
            'צ' => 'ц', 'ץ' => 'ц', 'ק' => 'к', 'ר' => 'р', 'ש' => 'ш', 'ת' => 'т',
        ];
        $out = '';
        $chars = preg_split('//u', $hebrew, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $c) {
            $out .= $map[$c] ?? $c;
        }
        return $out ?: '?';
    }

    protected function normalizeRoot(?string $input): string
    {
        if ($input === null || trim($input) === '') {
            return '';
        }

        $s = trim($input);
        $s = preg_replace('/[\x{05BE}\x{2010}\x{2011}\x{2013}\x{2014}\-\s]+/u', '-', $s);
        $s = preg_replace('/[\x{05B0}\x{05B1}\x{05B2}\x{05B3}\x{05B4}\x{05B5}\x{05B6}\x{05B7}\x{05B8}\x{05B9}\x{05BA}\x{05BB}\x{05BC}\x{05BD}\x{05BF}\x{05C1}\x{05C2}]/u', '', $s);
        $s = trim($s, '- ');
        return $s;
    }

    protected function extractPartOfSpeech(array $raw): array
    {
        $pos = [];
        $formType = $raw['form_type'] ?? null;
        $parsed = $raw['parsed_wikitext'] ?? null;

        if ($formType) {
            $normalized = self::$posMap[strtolower(trim($formType))] ?? null;
            if ($normalized) {
                $pos[] = $normalized;
            }
        }

        if ($parsed && isset($parsed['templates_flat'])) {
            foreach ($parsed['templates_flat'] as $t) {
                $name = strtolower($t['name'] ?? '');
                if (str_starts_with($name, 'he-noun')) {
                    $pos[] = 'noun';
                } elseif (str_starts_with($name, 'he-verb')) {
                    $pos[] = 'verb';
                } elseif (str_starts_with($name, 'he-adj')) {
                    $pos[] = 'adjective';
                } elseif (str_starts_with($name, 'he-interj')) {
                    $pos[] = 'interjection';
                } elseif (str_starts_with($name, 'he-proper')) {
                    $pos[] = 'proper_noun';
                } elseif (str_starts_with($name, 'he-adv')) {
                    $pos[] = 'adverb';
                }
            }
        }

        $pos = array_unique($pos);

        if (empty($pos)) {
            return ['noun'];
        }

        return array_values($pos);
    }

    protected function extractMeanings(array $raw, string $transliteration, string $hebrewWord): array
    {
        $ru = $raw['translations_ru'] ?? [];
        $all = is_array($ru) ? $ru : [$ru];

        $selfRefs = $this->getSelfReferences($transliteration, $hebrewWord, $raw);

        $cleaned = [];
        foreach ($all as $t) {
            $t = trim((string) $t);
            if ($t === '') {
                continue;
            }
            $t = $this->stripMetaLabel($t);
            if ($t === '') {
                continue;
            }
            if ($this->isSelfReference($t, $selfRefs)) {
                continue;
            }
            if (!in_array(strtolower($t), array_map('strtolower', $cleaned))) {
                $cleaned[] = $t;
            }
        }

        $usageWords = ['привет', 'здравствуй', 'пока', 'прощай', 'приветствие', 'прощание'];
        $coreWords = ['мир', 'покой', 'peace'];
        $extendedWords = ['отдых', 'благополучие', 'тишина', 'спокойствие'];

        $core = [];
        $extended = [];
        $usage = [];
        $other = [];

        foreach ($cleaned as $t) {
            $lower = strtolower($t);
            if (in_array($lower, $usageWords)) {
                $usage[] = $t;
            } elseif (in_array($lower, $coreWords)) {
                $core[] = $t;
            } elseif (in_array($lower, $extendedWords)) {
                $extended[] = $t;
            } else {
                $other[] = $t;
            }
        }

        if (empty($core) && !empty($other)) {
            $core = array_slice($other, 0, 1);
            $extended = array_merge($extended, array_slice($other, 1));
        } elseif (!empty($other)) {
            $extended = array_merge($extended, $other);
        }

        if (empty($core) && !empty($extended)) {
            $core = array_slice($extended, 0, 1);
            $extended = array_slice($extended, 1);
        }

        if (empty($core) && !empty($usage)) {
            $core = array_slice($usage, 0, 1);
            $usage = array_slice($usage, 1);
        }

        return array_filter([
            'core' => array_values(array_unique($core)),
            'extended' => array_values(array_unique($extended)),
            'usage' => array_values(array_unique($usage)),
        ]);
    }

    protected function getSelfReferences(string $transliteration, string $hebrewWord, array $raw = []): array
    {
        $refs = [$transliteration, $this->hebrewToLatin($hebrewWord)];
        $parsed = $raw['parsed_wikitext'] ?? null;
        if ($parsed && isset($parsed['templates_flat'])) {
            foreach ($parsed['templates_flat'] as $t) {
                if (($t['name'] ?? '') !== 'desc') {
                    continue;
                }
                $params = $t['params'] ?? [];
                $form = $params['2'] ?? $params[2] ?? null;
                if ($form !== null && trim($form) !== '') {
                    $refs[] = trim($form);
                }
            }
        }
        return array_filter(array_unique($refs));
    }

    protected function isSelfReference(string $translation, array $selfRefs): bool
    {
        $norm = $this->normalizeForCompare($translation);
        foreach ($selfRefs as $ref) {
            if ($norm === $this->normalizeForCompare($ref)) {
                return true;
            }
        }
        return false;
    }

    protected function normalizeForCompare(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/[\x{0301}\x{0300}\x{0308}]/u', '', $s);
        return $s;
    }

    protected function hebrewToLatin(string $hebrew): string
    {
        $hebrew = preg_replace('/[\x{05B0}\x{05B1}\x{05B2}\x{05B3}\x{05B4}\x{05B5}\x{05B6}\x{05B7}\x{05B8}\x{05B9}\x{05BA}\x{05BB}\x{05BC}\x{05BD}\x{05BF}\x{05C1}\x{05C2}]/u', '', $hebrew);
        $map = [
            'א' => "'", 'ב' => 'v', 'ג' => 'g', 'ד' => 'd', 'ה' => 'h', 'ו' => 'v', 'ז' => 'z',
            'ח' => 'kh', 'ט' => 't', 'י' => 'y', 'כ' => 'kh', 'ך' => 'kh', 'ל' => 'l', 'מ' => 'm',
            'ם' => 'm', 'נ' => 'n', 'ן' => 'n', 'ס' => 's', 'ע' => "'", 'פ' => 'p', 'ף' => 'f',
            'צ' => 'ts', 'ץ' => 'ts', 'ק' => 'k', 'ר' => 'r', 'ש' => 'sh', 'ת' => 't',
        ];
        $out = '';
        $chars = preg_split('//u', $hebrew, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $c) {
            $out .= $map[$c] ?? $c;
        }
        return strtolower($out);
    }

    protected function stripMetaLabel(string $t): string
    {
        foreach (self::$metaLabels as $label) {
            $t = preg_replace('/\b' . preg_quote($label, '/') . '\b/i', '', $t);
        }
        $t = preg_replace('/\s*[\(\[]\s*[^\)\]]*\s*[\)\]]\s*/u', '', $t);
        return trim($t, " \t\n\r\0\x0B,");
    }

    protected function detectGreeting(array $raw, array $meanings): bool
    {
        $usage = $meanings['usage'] ?? [];
        $all = array_merge($meanings['core'] ?? [], $meanings['extended'] ?? [], $usage);
        foreach ($all as $m) {
            if (in_array(strtolower($m), self::$greetingIndicators)) {
                return true;
            }
        }
        $parsed = $raw['parsed_wikitext'] ?? null;
        if ($parsed && isset($parsed['templates_flat'])) {
            foreach ($parsed['templates_flat'] as $t) {
                if (stripos($t['name'] ?? '', 'he-interj') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function detectName(array $raw, array $meanings): bool
    {
        $all = array_merge(
            $meanings['core'] ?? [],
            $meanings['extended'] ?? [],
            $meanings['usage'] ?? []
        );
        foreach ($all as $m) {
            if (in_array(strtolower($m), self::$nameIndicators)) {
                return true;
            }
        }
        $parsed = $raw['parsed_wikitext'] ?? null;
        if ($parsed && isset($parsed['templates_flat'])) {
            foreach ($parsed['templates_flat'] as $t) {
                if (stripos($t['name'] ?? '', 'he-proper') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function extractRelated(array $raw, string $root): array
    {
        if ($root === '') {
            return [];
        }
        return [];
    }

    protected function pruneEmpty(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (is_array($v)) {
                $v = $this->pruneEmpty($v);
                if (empty($v) && !in_array($k, ['meanings', 'part_of_speech'])) {
                    continue;
                }
            }
            $out[$k] = $v;
        }
        return $out;
    }
}
