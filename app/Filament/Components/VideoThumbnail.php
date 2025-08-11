<?php

namespace App\Filament\Components;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class VideoThumbnail extends TextColumn
{
    protected string $view = 'filament.components.video-thumbnail';

    public function getThumbnailUrl(): ?string
    {
        $videoUrl = $this->getState();
        
        if (empty($videoUrl)) {
            return null;
        }

        // Extract video ID from YouTube URL
        $videoId = $this->getYoutubeVideoId($videoUrl);
        
        if ($videoId) {
            return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
        }

        return null;
    }

    public function getVideoUrl(): ?string
    {
        return $this->getState();
    }

    protected function getYoutubeVideoId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getEmbedUrl(): ?string
    {
        $videoUrl = $this->getState();
        
        if (empty($videoUrl)) {
            return null;
        }

        $videoId = $this->getYoutubeVideoId($videoUrl);
        
        if ($videoId) {
            return "https://www.youtube.com/embed/{$videoId}";
        }

        return null;
    }
}