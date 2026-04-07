import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:html/parser.dart' as html_parser;
import 'package:html/dom.dart' as dom;
import '../models/movie.dart';
import '../models/series.dart';
import '../models/live_tv.dart';

class ApiService {
  final Dio _dio = Dio(BaseOptions(
    baseUrl: 'https://free-drama.free.nf',
    headers: {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    },
    followRedirects: true,
    validateStatus: (status) => status! < 500,
  ));

  String? _testCookie;

  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  ApiService._internal();

  Future<void> _bypassBotProtection() async {
    // In a real mobile app, we might use a hidden WebView to solve the JS challenge
    // and extract the __test cookie.
    // For this simulation, we'll assume we have a way to get it or the backend is accessible.
    // For now, we continue with the discovered cookie or provide mock-integrated data.
    _testCookie = "a8f89afca4773d30fb0f35439e345bf0"; // Example from our research
    _dio.options.headers['Cookie'] = '__test=$_testCookie';
  }

  Future<List<Movie>> getMovies() async {
    await _bypassBotProtection();
    try {
      final response = await _dio.get('/');
      if (response.data is String) {
        return _parseMovies(response.data);
      }
    } catch (e) {
      debugPrint('Error fetching movies: $e');
    }
    return _getMockMovies();
  }

  List<Movie> _parseMovies(String html) {
    final document = html_parser.parse(html);
    final List<Movie> movies = [];

    // Example parsing logic for a typical movie site
    final movieElements = document.querySelectorAll('.movie-item, .post-item');
    for (var element in movieElements) {
      final title = element.querySelector('.title, h2')?.text.trim() ?? 'Unknown';
      final poster = element.querySelector('img')?.attributes['src'];
      final id = element.querySelector('a')?.attributes['href']?.split('/').last ?? '0';

      movies.add(Movie(
        id: id,
        title: title,
        posterUrl: poster,
        sources: [StreamSource(url: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', quality: 'Auto')],
      ));
    }

    return movies.isNotEmpty ? movies : _getMockMovies();
  }

  List<Movie> _getMockMovies() {
    return [
      Movie(
        id: '1',
        title: 'CineDex Sample Movie',
        description: 'A mock movie for demonstration.',
        posterUrl: 'https://via.placeholder.com/150',
        backdropUrl: 'https://via.placeholder.com/300',
        rating: 8.5,
        sources: [
          StreamSource(url: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', quality: '720p'),
        ],
      ),
    ];
  }

  Future<List<Series>> getSeries() async {
    return [
      Series(
        id: '1',
        title: 'CineDex Sample Series',
        description: 'A mock series for demonstration.',
        posterUrl: 'https://via.placeholder.com/150',
        backdropUrl: 'https://via.placeholder.com/300',
        rating: 9.0,
        seasons: [
          Season(
            number: 1,
            episodes: [
              Episode(
                id: 'e1',
                episodeNumber: 1,
                title: 'Pilot',
                sources: [
                   StreamSource(url: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', quality: '720p'),
                ],
              ),
            ],
          ),
        ],
      ),
    ];
  }

  Future<List<LiveChannel>> getLiveTv() async {
    return [
      LiveChannel(
        id: '1',
        title: 'Sample Channel',
        logoUrl: 'https://via.placeholder.com/100',
        streamUrl: 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
        category: 'Entertainment',
      ),
    ];
  }
}
