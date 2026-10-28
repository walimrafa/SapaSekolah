<?php

namespace App\Services;

use App\Models\District;
use App\Models\MarketingRoute;
use App\Models\RouteSchool;
use App\Models\School;
use App\Models\User;

class RouteClusteringService
{
    /**
     * Generate an optimized daily car driving route cluster of up to 10 nearby schools.
     * Uses Nearest-Neighbor TSP algorithm starting from UBSI Karawang campus.
     */
    public function generateDailyCluster(array $schoolIds = [], array|string|null $districtIds = null, int $limit = 10, array $excludeSchoolIds = [], ?string $startSchoolId = null, ?string $endSchoolId = null, ?int $regencyId = null): array
    {
        $ubsiLat = SchoolScoringService::UBSI_LATITUDE;
        $ubsiLng = SchoolScoringService::UBSI_LONGITUDE;

        // Normalize to array
        if (is_string($districtIds)) {
            $districtIds = [$districtIds];
        }
        $districtIds = array_filter($districtIds ?? []);

        $query = School::with(['district', 'regency'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (!empty($excludeSchoolIds)) {
            $query->whereNotIn('id', $excludeSchoolIds);
        }

        if (!empty($schoolIds)) {
            $query->whereIn('id', $schoolIds);
        }

        if (!empty($regencyId)) {
            $query->where(function ($rq) use ($regencyId) {
                $rq->where('regency_id', $regencyId)
                   ->orWhereHas('district', fn($dq) => $dq->where('regency_id', $regencyId));
            });
        }

        if (!empty($districtIds)) {
            $query->whereIn('district_id', $districtIds);
        }

        $availableSchools = $query->orderBy('potential_score', 'desc')->get()->toArray();

        if (empty($availableSchools)) {
            return [];
        }

        $routeSequence = [];
        $currentLat = $ubsiLat;
        $currentLng = $ubsiLng;
        $totalDistance = 0.0;
        $startLocationName = 'Kampus UBSI Karawang (Jl. Banten No.1)';
        $endLocationName = 'Kampus UBSI Karawang (Jl. Banten No.1)';

        // Separate fixed ending school if specified
        $fixedEndSchool = null;
        if (!empty($endSchoolId) && !in_array($endSchoolId, ['ubsi', 'no_return'])) {
            foreach ($availableSchools as $idx => $s) {
                if ((string)$s['id'] === (string)$endSchoolId) {
                    $fixedEndSchool = $s;
                    array_splice($availableSchools, $idx, 1);
                    break;
                }
            }
        }

        // Handle custom start school if specified
        if (!empty($startSchoolId) && $startSchoolId !== 'ubsi') {
            $startIdx = null;

            if ($startSchoolId === 'top_potential') {
                // Pick highest potential school in the available list
                $startIdx = 0;
            } else {
                // Find index of specified school ID
                foreach ($availableSchools as $idx => $s) {
                    if ((string)$s['id'] === (string)$startSchoolId) {
                        $startIdx = $idx;
                        break;
                    }
                }
            }

            if ($startIdx !== null && isset($availableSchools[$startIdx])) {
                $firstSchool = $availableSchools[$startIdx];
                $distFromUbsi = $this->haversine($ubsiLat, $ubsiLng, (float)$firstSchool['latitude'], (float)$firstSchool['longitude']);
                
                $totalDistance += $distFromUbsi;
                $currentLat = (float)$firstSchool['latitude'];
                $currentLng = (float)$firstSchool['longitude'];

                $firstSchool['leg_distance'] = round($distFromUbsi, 2);
                $routeSequence[] = $firstSchool;
                $startLocationName = $firstSchool['name'] . ' (' . ($firstSchool['district'] ? 'Kec. ' . $firstSchool['district']['name'] : '') . ')';

                array_splice($availableSchools, $startIdx, 1);
            }
        }

        // Target count for intermediate nearest neighbor loop
        $targetLimit = $fixedEndSchool ? max(1, $limit - 1) : $limit;

        // Pick remaining nearest schools iteratively using TSP Nearest Neighbor
        while (count($routeSequence) < $targetLimit && !empty($availableSchools)) {
            $nearestIdx = null;
            $minDist = 999999;

            foreach ($availableSchools as $idx => $s) {
                $dist = $this->haversine($currentLat, $currentLng, (float)$s['latitude'], (float)$s['longitude']);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            if ($nearestIdx !== null) {
                $chosen = $availableSchools[$nearestIdx];
                $totalDistance += $minDist;
                $currentLat = (float)$chosen['latitude'];
                $currentLng = (float)$chosen['longitude'];
                
                $chosen['leg_distance'] = round($minDist, 2);
                $routeSequence[] = $chosen;

                array_splice($availableSchools, $nearestIdx, 1);
            }
        }

        // Append fixed ending school if set
        if ($fixedEndSchool) {
            $distToEnd = $this->haversine($currentLat, $currentLng, (float)$fixedEndSchool['latitude'], (float)$fixedEndSchool['longitude']);
            $totalDistance += $distToEnd;
            $currentLat = (float)$fixedEndSchool['latitude'];
            $currentLng = (float)$fixedEndSchool['longitude'];

            $fixedEndSchool['leg_distance'] = round($distToEnd, 2);
            $routeSequence[] = $fixedEndSchool;
            $endLocationName = $fixedEndSchool['name'] . ' (' . ($fixedEndSchool['district'] ? 'Kec. ' . $fixedEndSchool['district']['name'] : '') . ')';
        }

        // Return trip calculation
        if ($endSchoolId === 'no_return') {
            // End at the last school without returning to UBSI
            $lastSchool = end($routeSequence);
            if ($lastSchool) {
                $endLocationName = $lastSchool['name'] . ' (Stop Akhir)';
            }
        } else {
            // Return trip to UBSI Karawang
            $returnDist = $this->haversine($currentLat, $currentLng, $ubsiLat, $ubsiLng);
            $totalDistance += $returnDist;
            if (!$fixedEndSchool) {
                $endLocationName = 'Kampus UBSI Karawang (Jl. Banten No.1)';
            }
        }

        // Car driving speed average 30 km/h in Karawang + 25 mins meeting per school
        $drivingMinutes = round(($totalDistance / 30) * 60);
        $meetingMinutes = count($routeSequence) * 25;
        $totalMinutes = $drivingMinutes + $meetingMinutes;

        // Build Google Maps Driving Directions Link
        $gmapsUrl = $this->buildGoogleMapsDrivingUrl($routeSequence);

        return [
            'start_location' => $startLocationName,
            'end_location' => $endLocationName,
            'schools_count' => count($routeSequence),
            'total_distance_km' => round($totalDistance, 2),
            'estimated_duration_minutes' => $totalMinutes,
            'driving_minutes' => $drivingMinutes,
            'meeting_minutes' => $meetingMinutes,
            'google_maps_driving_url' => $gmapsUrl,
            'sequence' => $routeSequence,
        ];
    }

    /**
     * Build Google Maps Driving Directions URL for car navigation.
     */
    public function buildGoogleMapsDrivingUrl(array $sequence): string
    {
        $ubsi = SchoolScoringService::UBSI_LATITUDE . ',' . SchoolScoringService::UBSI_LONGITUDE;
        
        if (empty($sequence)) {
            return "https://www.google.com/maps/dir/?api=1&origin={$ubsi}&destination={$ubsi}&travelmode=driving";
        }

        $waypoints = [];
        foreach ($sequence as $s) {
            $waypoints[] = $s['latitude'] . ',' . $s['longitude'];
        }

        $origin = $ubsi;
        $destination = $ubsi; // Loop back to UBSI Karawang
        $waypointsStr = implode('|', $waypoints);

        return "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$destination}&waypoints={$waypointsStr}&travelmode=driving";
    }

    /**
     * Generate complete 10-Day Outing Campaign (Hari 1 s/d Hari 10) with 10 schools per day.
     */
    public function generate10DayCampaign(int $userId): array
    {
        $usedSchoolIds = [];
        $createdRoutes = [];

        $districts = District::activeDistricts()->orderBy('name')->get();
        $startDate = today();

        for ($day = 1; $day <= 10; $day++) {
            $currentDate = (clone $startDate)->addDays($day - 1);
            
            // Pick a district or general unvisited schools
            $district = $districts[$day - 1] ?? null;
            $districtId = $district ? $district->id : null;
            $districtName = $district ? $district->name : 'Karawang';

            $cluster = $this->generateDailyCluster([], $districtId, 10, $usedSchoolIds);

            // If empty, search without district filter
            if (empty($cluster['sequence'])) {
                $cluster = $this->generateDailyCluster([], null, 10, $usedSchoolIds);
            }

            if (!empty($cluster['sequence'])) {
                foreach ($cluster['sequence'] as $s) {
                    $usedSchoolIds[] = $s['id'];
                }

                $routeName = "Outing Mobil Hari #{$day} - Klaster {$districtName} (10 Sekolah)";

                $route = MarketingRoute::create([
                    'name' => $routeName,
                    'date' => $currentDate->format('Y-m-d'),
                    'user_id' => $userId,
                    'start_location' => 'Kampus UBSI Karawang (Jl. Banten No.1)',
                    'end_location' => 'Kampus UBSI Karawang (Jl. Banten No.1)',
                    'total_distance' => $cluster['total_distance_km'],
                    'estimated_duration' => $cluster['estimated_duration_minutes'],
                    'status' => 'planned',
                ]);

                foreach ($cluster['sequence'] as $idx => $s) {
                    RouteSchool::create([
                        'route_id' => $route->id,
                        'school_id' => $s['id'],
                        'sequence' => $idx + 1,
                        'status' => 'pending',
                    ]);
                }

                $createdRoutes[] = [
                    'day' => $day,
                    'route_id' => $route->id,
                    'name' => $routeName,
                    'date' => $currentDate->format('d M Y'),
                    'schools_count' => count($cluster['sequence']),
                    'distance_km' => $cluster['total_distance_km'],
                    'duration_min' => $cluster['estimated_duration_minutes'],
                    'gmaps_url' => $cluster['google_maps_driving_url'],
                ];
            }
        }

        return $createdRoutes;
    }

    private function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
