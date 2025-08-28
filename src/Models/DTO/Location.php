<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 位置对象
 * 
 * 表示地理位置的数据传输对象
 */
class Location extends BaseDTO implements DTOInterface
{
    /**
     * 经度，由发送方定义
     */
    public readonly float $longitude;

    /**
     * 纬度，由发送方定义
     */
    public readonly float $latitude;

    /**
     * 位置的水平精度（米，可选）
     */
    public readonly ?float $horizontalAccuracy;

    /**
     * 实时位置消息的生存时间（秒，可选）
     */
    public readonly ?int $livePeriod;

    /**
     * 实时位置的方向（度，1-360，可选）
     */
    public readonly ?int $heading;

    /**
     * 接近警报的最大距离（米，可选）
     */
    public readonly ?int $proximityAlertRadius;

    public function __construct(
        float $longitude,
        float $latitude,
        ?float $horizontalAccuracy = null,
        ?int $livePeriod = null,
        ?int $heading = null,
        ?int $proximityAlertRadius = null
    ) {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->horizontalAccuracy = $horizontalAccuracy;
        $this->livePeriod = $livePeriod;
        $this->heading = $heading;
        $this->proximityAlertRadius = $proximityAlertRadius;

        parent::__construct();
    }

    /**
     * 从数组创建 Location 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            longitude: (float) ($data['longitude'] ?? 0),
            latitude: (float) ($data['latitude'] ?? 0),
            horizontalAccuracy: isset($data['horizontal_accuracy']) ? (float) $data['horizontal_accuracy'] : null,
            livePeriod: isset($data['live_period']) ? (int) $data['live_period'] : null,
            heading: isset($data['heading']) ? (int) $data['heading'] : null,
            proximityAlertRadius: isset($data['proximity_alert_radius']) ? (int) $data['proximity_alert_radius'] : null
        );
    }

    /**
     * 验证位置数据
     */
    public function validate(): void
    {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }

        if ($this->horizontalAccuracy !== null && $this->horizontalAccuracy < 0) {
            throw new \InvalidArgumentException('Horizontal accuracy must be non-negative');
        }

        if ($this->livePeriod !== null && ($this->livePeriod < 60 || $this->livePeriod > 86400)) {
            throw new \InvalidArgumentException('Live period must be between 60 and 86400 seconds');
        }

        if ($this->heading !== null && ($this->heading < 1 || $this->heading > 360)) {
            throw new \InvalidArgumentException('Heading must be between 1 and 360 degrees');
        }

        if ($this->proximityAlertRadius !== null && ($this->proximityAlertRadius < 1 || $this->proximityAlertRadius > 100000)) {
            throw new \InvalidArgumentException('Proximity alert radius must be between 1 and 100000 meters');
        }
    }

    /**
     * 获取格式化的坐标字符串
     */
    public function getCoordinatesString(): string
    {
        return sprintf('%.6f, %.6f', $this->latitude, $this->longitude);
    }

    /**
     * 获取 Google Maps URL
     */
    public function getGoogleMapsUrl(): string
    {
        return sprintf(
            'https://www.google.com/maps/search/?api=1&query=%f,%f',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * 获取 OpenStreetMap URL
     */
    public function getOpenStreetMapUrl(): string
    {
        return sprintf(
            'https://www.openstreetmap.org/?mlat=%f&mlon=%f&zoom=15',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * 计算到另一个位置的距离（米）
     * 使用 Haversine 公式
     */
    public function getDistanceTo(Location $other): float
    {
        $earthRadius = 6371000; // 地球半径（米）
        
        $latDiff = deg2rad($other->latitude - $this->latitude);
        $lonDiff = deg2rad($other->longitude - $this->longitude);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * 获取格式化的距离字符串
     */
    public function getDistanceToFormatted(Location $other): string
    {
        $distance = $this->getDistanceTo($other);
        
        if ($distance < 1000) {
            return sprintf('%.0f m', $distance);
        }
        
        return sprintf('%.1f km', $distance / 1000);
    }

    /**
     * 计算方位角（从当前位置到目标位置的角度）
     */
    public function getBearingTo(Location $other): float
    {
        $lat1 = deg2rad($this->latitude);
        $lat2 = deg2rad($other->latitude);
        $lonDiff = deg2rad($other->longitude - $this->longitude);
        
        $y = sin($lonDiff) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lonDiff);
        
        $bearing = rad2deg(atan2($y, $x));
        
        return fmod($bearing + 360, 360); // 确保结果在 0-360 度范围内
    }

    /**
     * 获取方向描述
     */
    public function getDirectionTo(Location $other): string
    {
        $bearing = $this->getBearingTo($other);
        
        $directions = [
            'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
        ];
        
        $index = (int) round($bearing / 22.5) % 16;
        
        return $directions[$index];
    }

    /**
     * 检查是否在指定的地理边界内
     */
    public function isWithinBounds(float $minLat, float $maxLat, float $minLon, float $maxLon): bool
    {
        return $this->latitude >= $minLat && $this->latitude <= $maxLat &&
               $this->longitude >= $minLon && $this->longitude <= $maxLon;
    }

    /**
     * 检查是否为实时位置
     */
    public function isLiveLocation(): bool
    {
        return $this->livePeriod !== null;
    }

    /**
     * 检查是否有方向信息
     */
    public function hasHeading(): bool
    {
        return $this->heading !== null;
    }

    /**
     * 检查是否有接近警报
     */
    public function hasProximityAlert(): bool
    {
        return $this->proximityAlertRadius !== null;
    }

    /**
     * 获取方向描述（根据 heading）
     */
    public function getHeadingDirection(): ?string
    {
        if ($this->heading === null) {
            return null;
        }
        
        $directions = [
            'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
        ];
        
        $index = (int) round($this->heading / 22.5) % 16;
        
        return $directions[$index];
    }

    /**
     * 获取实时位置剩余时间（秒）
     */
    public function getRemainingLiveTime(): ?int
    {
        if (!$this->isLiveLocation()) {
            return null;
        }
        
        // 注意：这里需要知道位置发送的时间才能计算剩余时间
        // 在实际使用中，应该从消息的时间戳计算
        return $this->livePeriod;
    }

    /**
     * 检查两个位置是否相近（在指定距离内）
     */
    public function isNearby(Location $other, float $maxDistance = 100): bool
    {
        return $this->getDistanceTo($other) <= $maxDistance;
    }

    /**
     * 获取位置的完整信息
     */
    public function getLocationInfo(): array
    {
        return [
            'coordinates' => $this->getCoordinatesString(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_live' => $this->isLiveLocation(),
            'has_heading' => $this->hasHeading(),
            'has_proximity_alert' => $this->hasProximityAlert(),
            'horizontal_accuracy' => $this->horizontalAccuracy,
            'live_period' => $this->livePeriod,
            'heading' => $this->heading,
            'heading_direction' => $this->getHeadingDirection(),
            'proximity_alert_radius' => $this->proximityAlertRadius,
            'google_maps_url' => $this->getGoogleMapsUrl(),
            'openstreetmap_url' => $this->getOpenStreetMapUrl(),
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [$this->getCoordinatesString()];
        
        if ($this->horizontalAccuracy !== null) {
            $info[] = "±{$this->horizontalAccuracy}m";
        }
        
        if ($this->isLiveLocation()) {
            $info[] = "Live ({$this->livePeriod}s)";
        }
        
        if ($this->hasHeading()) {
            $info[] = "Heading: {$this->getHeadingDirection()}";
        }
        
        return implode(' - ', $info);
    }
}