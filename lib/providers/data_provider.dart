import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/movie.dart';
import '../models/series.dart';
import '../models/live_tv.dart';
import '../services/api_service.dart';

class DataProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<Movie> _movies = [];
  List<Series> _series = [];
  List<LiveChannel> _liveChannels = [];
  bool _isLoading = false;

  List<Movie> get movies => _movies;
  List<Series> get series => _series;
  List<LiveChannel> get liveChannels => _liveChannels;
  bool get isLoading => _isLoading;

  Future<void> fetchData() async {
    _isLoading = true;
    notifyListeners();

    try {
      _movies = await _apiService.getMovies();
      _series = await _apiService.getSeries();
      _liveChannels = await _apiService.getLiveTv();
    } catch (e) {
      debugPrint('Error fetching data: $e');
    }

    _isLoading = false;
    notifyListeners();
  }
}
