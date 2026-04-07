import 'package:flutter_test/flutter_test.dart';
import 'package:cinedex/models/movie.dart';

void main() {
  test('Movie model fromJson and toJson', () {
    final json = {
      'id': '1',
      'title': 'Test Movie',
      'description': 'Test Description',
      'posterUrl': 'https://example.com/poster.jpg',
      'backdropUrl': 'https://example.com/backdrop.jpg',
      'rating': 9.0,
      'releaseDate': '2025-01-01',
      'genres': ['Action', 'Sci-Fi'],
      'sources': [
        {'url': 'https://example.com/stream.m3u8', 'quality': '1080p', 'isDrm': false}
      ]
    };

    final movie = Movie.fromJson(json);
    expect(movie.id, '1');
    expect(movie.title, 'Test Movie');
    expect(movie.sources?.first.url, 'https://example.com/stream.m3u8');

    final backToJson = movie.toJson();
    expect(backToJson['id'], '1');
  });
}
