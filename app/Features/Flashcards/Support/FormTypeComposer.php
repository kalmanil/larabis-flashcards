<?php

namespace App\Features\Flashcards\Support;

/**
 * Builds canonical `form_type` strings from factorized LLM fields (pos + modifiers).
 * Output strings must match {@see FormTypeCatalog::allowed()}.
 */
final class FormTypeComposer
{
    /**
     * @param  array<string, mixed>  $entry
     */
    public static function compose(array $entry): ?string
    {
        if (! isset($entry['pos_id']) || ! is_numeric($entry['pos_id'])) {
            return null;
        }

        $pos = (int) $entry['pos_id'];

        return match ($pos) {
            1 => self::composeNoun($entry, false),
            2 => self::composeVerb($entry),
            3 => self::composeAdjective($entry),
            4 => 'adverb',
            5 => 'preposition',
            6 => 'conjunction',
            7 => 'pronoun',
            8 => 'particle',
            9 => 'interjection',
            10 => self::composeNoun($entry, true),
            11 => 'numeral',
            12 => 'phrase',
            13 => 'name',
            14 => 'abbreviation',
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private static function composeNoun(array $entry, bool $proper): ?string
    {
        if ($proper) {
            return 'proper noun';
        }

        $kind = self::intOrZero($entry['noun_kind_id'] ?? 0);
        if ($kind === 2) {
            return 'noun (uncountable)';
        }

        $gender = self::intOrZero($entry['noun_gender_id'] ?? 0);
        $number = self::intOrZero($entry['noun_number_id'] ?? 0);

        if ($number === 3) {
            return 'noun (masc. pl.)';
        }
        if ($number === 4) {
            return 'noun (fem. pl.)';
        }
        if ($number === 2) {
            return 'noun (pl.)';
        }

        if ($gender === 1) {
            return 'noun (masc.)';
        }
        if ($gender === 2) {
            return 'noun (fem.)';
        }

        return 'noun';
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private static function composeAdjective(array $entry): ?string
    {
        $gender = self::intOrZero($entry['adj_gender_id'] ?? 0);
        $number = self::intOrZero($entry['adj_number_id'] ?? 0);

        if ($gender === 1 && $number === 3) {
            return 'adjective (masc. pl.)';
        }
        if ($gender === 2 && $number === 4) {
            return 'adjective (fem. pl.)';
        }
        if ($gender === 1) {
            return 'adjective (masc.)';
        }
        if ($gender === 2) {
            return 'adjective (fem.)';
        }

        return 'adjective';
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private static function composeVerb(array $entry): ?string
    {
        $shape = self::intOrZero($entry['verb_shape_id'] ?? 0);
        $binyan = self::intOrZero($entry['verb_binyan_id'] ?? 0);

        if ($shape === 0) {
            return 'verb';
        }

        if ($shape === 1 || $shape === 2 || $shape === 15) {
            $binyanLabel = self::binyanLabel($binyan);
            if ($binyanLabel === null) {
                return null;
            }
            if ($shape === 1) {
                return 'verb - '.$binyanLabel;
            }

            return 'verb - '.$binyanLabel.' infinitive';
        }

        $suffix = match ($shape) {
            3 => 'past (3ms)',
            4 => 'past (3fs)',
            5 => 'present (ms)',
            6 => 'present (fs)',
            7 => 'present (mpl)',
            8 => 'present (fpl)',
            9 => 'imperative (ms)',
            10 => 'imperative (fs)',
            11 => 'participle (ms)',
            12 => 'participle (fs)',
            13 => 'passive participle (ms)',
            14 => 'passive participle (fs)',
            default => null,
        };

        if ($suffix === null) {
            return null;
        }

        $binyanLabel = self::binyanLabel($binyan);
        if ($binyanLabel === null) {
            return null;
        }

        return 'verb - '.$binyanLabel.' '.$suffix;
    }

    private static function binyanLabel(int $id): ?string
    {
        return match ($id) {
            1 => 'qal',
            2 => 'nif\'al',
            3 => 'pi\'el',
            4 => 'pu\'al',
            5 => 'hif\'il',
            6 => 'huf\'al',
            7 => 'hitpa\'el',
            default => null,
        };
    }

    private static function intOrZero(mixed $v): int
    {
        if ($v === null || $v === '' || ! is_numeric($v)) {
            return 0;
        }

        return (int) $v;
    }

    /**
     * Compact factor tables for LLM system prompts (no pre-merged form_type list).
     */
    public static function promptFactorTables(): string
    {
        return <<<'TXT'
pos_id (required): 1=noun 2=verb 3=adjective 4=adverb 5=preposition 6=conjunction 7=pronoun 8=particle 9=interjection 10=proper noun 11=numeral 12=phrase 13=name 14=abbreviation

Omit modifier keys that are not applicable to this sense's pos_id (do not send 0 or null for unrelated parts).

For pos_id 1 (noun): optional noun_gender_id 1=masc 2=fem 3=unspecified; noun_number_id 1=default sg 2=pl. 3=masc pl. 4=fem pl.; noun_kind_id 1=common (default) 2=uncountable
For pos_id 10 (proper noun): no noun_* modifiers; always proper noun label.

For pos_id 3 (adjective): optional adj_gender_id 1=masc 2=fem 3=unspecified; adj_number_id 1=default sg 2=pl 3=masc pl. 4=fem pl.

For pos_id 2 (verb): verb_binyan_id REQUIRED whenever verb_shape_id is not 0 — 1=qal 2=nif'al 3=pi'el 4=pu'al 5=hif'il 6=huf'al 7=hitpa'el
verb_shape_id (required for verb): 1=stem (verb - {binyan}) 2=infinitive (verb - {binyan} infinitive) 3=past 3ms 4=past 3fs 5=present ms 6=present fs 7=present mpl 8=present fpl 9=imperative ms 10=imperative fs 11=participle ms 12=participle fs 13=passive participle ms 14=passive participle fs 15=infinitive (same as 2; include verb_binyan_id) 0=simple "verb". Finite/participle shapes: verb - {binyan} {tense/participle}, e.g. verb - huf'al past (3ms).

Do not output form_type or form_type_id. Use only pos_id + modifier fields.
TXT;
    }

    /**
     * Every distinct `form_type` string {@see compose()} can produce (for validation / UI / datalist).
     *
     * @return list<string>
     */
    public static function allComposedLabels(): array
    {
        $set = [];

        for ($pos = 1; $pos <= 14; $pos++) {
            if ($pos === 1) {
                foreach ([0, 1, 2] as $kind) {
                    foreach ([0, 1, 2, 3, 4] as $num) {
                        foreach ([0, 1, 2, 3] as $gen) {
                            $s = self::compose([
                                'pos_id' => 1,
                                'noun_kind_id' => $kind,
                                'noun_number_id' => $num,
                                'noun_gender_id' => $gen,
                            ]);
                            if ($s !== null) {
                                $set[$s] = true;
                            }
                        }
                    }
                }

                continue;
            }

            if ($pos === 2) {
                for ($shape = 0; $shape <= 15; $shape++) {
                    for ($bin = 0; $bin <= 7; $bin++) {
                        $s = self::compose([
                            'pos_id' => 2,
                            'verb_shape_id' => $shape,
                            'verb_binyan_id' => $bin,
                        ]);
                        if ($s !== null) {
                            $set[$s] = true;
                        }
                    }
                }

                continue;
            }

            if ($pos === 3) {
                foreach ([0, 1, 2, 3] as $ag) {
                    foreach ([0, 1, 2, 3, 4] as $an) {
                        $s = self::compose([
                            'pos_id' => 3,
                            'adj_gender_id' => $ag,
                            'adj_number_id' => $an,
                        ]);
                        if ($s !== null) {
                            $set[$s] = true;
                        }
                    }
                }

                continue;
            }

            $s = self::compose(['pos_id' => $pos]);
            if ($s !== null) {
                $set[$s] = true;
            }
        }

        $labels = array_keys($set);
        sort($labels);

        return array_values($labels);
    }
}
