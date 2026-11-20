import 'dart:math';

class LocationService {
  // Returns GPS coordinates for simulation
  static Map<String, double> getMockLocation({required bool withinFence}) {
    if (withinFence) {
      // Ministry of Youth & Sports Headquarters: 6.3156, -10.8074
      // Return something extremely close (e.g. within 10 meters)
      final offset = (Random().nextDouble() - 0.5) * 0.0001;
      return {
        'latitude': 6.3156 + offset,
        'longitude': -10.8074 + offset,
      };
    } else {
      // Returns coordinates in center of Monrovia/elsewhere (outside 100 meters range)
      return {
        'latitude': 6.2000,
        'longitude': -10.7000,
      };
    }
  }
}
