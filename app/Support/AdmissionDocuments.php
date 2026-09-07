<?php

namespace App\Support;

class AdmissionDocuments
{
    public const FORM_137 = 'form_137';

    public const GOOD_MORAL = 'good_moral_certificate';

    public const BIRTH_CERTIFICATE = 'birth_certificate';

    public const ACCOMPLISHMENT_CREDENTIALS = 'accomplishment_credentials';

    public static function isSeniorHigh(?string $yearLevel): bool
    {
        $normalized = strtolower(trim((string) $yearLevel));

        return str_contains($normalized, 'grade 11')
            || str_contains($normalized, 'grade 12')
            || str_contains($normalized, 'senior');
    }

    public static function isIncomingGrade7(?string $yearLevel): bool
    {
        $normalized = strtolower(trim((string) $yearLevel));

        return str_contains($normalized, 'grade 7');
    }

    public static function category(?string $yearLevel): string
    {
        if (self::isSeniorHigh($yearLevel)) {
            return 'senior_high';
        }

        if (self::isIncomingGrade7($yearLevel)) {
            return 'incoming_grade_7';
        }

        return 'transferee';
    }

    /**
     * @return list<string>
     */
    public static function typesForYearLevel(?string $yearLevel): array
    {
        $common = [
            self::BIRTH_CERTIFICATE,
        ];

        if (self::isSeniorHigh($yearLevel)) {
            return array_merge([self::ACCOMPLISHMENT_CREDENTIALS], $common);
        }

        // Incoming Grade 7 and transferees (Grade 8-10)
        return array_merge([self::FORM_137, self::GOOD_MORAL], $common);
    }

    /**
     * @return array<string, array{label: string, short: string, description: string, accept: string, format: string, mimes: string}>
     */
    public static function catalog(): array
    {
        return [
            self::FORM_137 => [
                'label' => 'Form 137 (Permanent Record)',
                'short' => 'Form 137',
                'description' => 'Academic records from previous school (incoming Grade 7 and transferees)',
                'accept' => 'application/pdf',
                'format' => 'PDF',
                'mimes' => 'pdf',
            ],
            self::GOOD_MORAL => [
                'label' => 'Good Moral Certificate',
                'short' => 'Good Moral',
                'description' => 'Certificate of Good Moral Character from previous school (incoming Grade 7 and transferees)',
                'accept' => 'application/pdf',
                'format' => 'PDF',
                'mimes' => 'pdf',
            ],
            self::ACCOMPLISHMENT_CREDENTIALS => [
                'label' => 'Accomplishment Credentials',
                'short' => 'Accomplishment Credentials',
                'description' => 'Accomplishment credentials required for Senior High School (Grade 11-12)',
                'accept' => 'application/pdf',
                'format' => 'PDF',
                'mimes' => 'pdf',
            ],
            self::BIRTH_CERTIFICATE => [
                'label' => 'PSA Birth Certificate',
                'short' => 'Birth Certificate',
                'description' => 'Original PSA/NSO certified birth certificate',
                'accept' => 'application/pdf',
                'format' => 'PDF',
                'mimes' => 'pdf',
            ],
        ];
    }

    /**
     * @return list<array{key: string, label: string, short: string, description: string, accept: string, format: string, mimes: string}>
     */
    public static function documentsForYearLevel(?string $yearLevel): array
    {
        $catalog = self::catalog();

        return array_map(
            function (string $type) use ($catalog) {
                return [
                    'key' => $type,
                    ...$catalog[$type],
                ];
            },
            self::typesForYearLevel($yearLevel)
        );
    }

    /**
     * @return list<string>
     */
    public static function allowedTypes(): array
    {
        return array_keys(self::catalog());
    }
}
