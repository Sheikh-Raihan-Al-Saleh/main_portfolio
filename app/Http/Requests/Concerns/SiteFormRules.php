<?php

namespace App\Http\Requests\Concerns;

use App\Services\SocialLink\SocialLinkService;

/**
 * Rules shared by the Studio and Founder admin forms.
 */
trait SiteFormRules
{
    /**
     * The { platform: url } social links map.
     *
     * @return array<string, array<int, string>>
     */
    protected function socialRules(): array
    {
        $rules = ['socials' => ['nullable', 'array']];

        foreach (SocialLinkService::PLATFORMS as $platform) {
            // WhatsApp links are often wa.me/... and still valid URLs.
            $rules["socials.{$platform}"] = ['nullable', 'url', 'max:255'];
        }

        return $rules;
    }

    /**
     * The footer editor payload.
     *
     * @return array<string, array<int, string>>
     */
    protected function footerRules(): array
    {
        return [
            'footer' => ['nullable', 'array'],
            'footer.status_text' => ['nullable', 'string', 'max:120'],
            'footer.status_text_unavailable' => ['nullable', 'string', 'max:120'],
            'footer.copyright' => ['nullable', 'string', 'max:255'],
            'footer.back_to_top' => ['nullable', 'string', 'max:80'],
            'footer.columns' => ['nullable', 'array', 'max:6'],
            'footer.columns.*.title' => ['required', 'string', 'max:120'],
            'footer.columns.*.links' => ['nullable', 'array', 'max:12'],
            'footer.columns.*.links.*.label' => ['required', 'string', 'max:120'],
            'footer.columns.*.links.*.url' => ['nullable', 'string', 'max:255'],
            'footer.legal_links' => ['nullable', 'array', 'max:12'],
            'footer.legal_links.*.label' => ['required', 'string', 'max:120'],
            'footer.legal_links.*.url' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function seoRules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'remove_og_image' => ['boolean'],
        ];
    }
}
