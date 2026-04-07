import 'movie.dart';

class Series {
  final String id;
  final String title;
  final String? description;
  final String? posterUrl;
  final String? backdropUrl;
  final double? rating;
  final List<Season> seasons;

  Series({
    required this.id,
    required this.title,
    this.description,
    this.posterUrl,
    this.backdropUrl,
    this.rating,
    required this.seasons,
  });

  factory Series.fromJson(Map<String, dynamic> json) {
    return Series(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      posterUrl: json['posterUrl'],
      backdropUrl: json['backdropUrl'],
      rating: json['rating']?.toDouble(),
      seasons: (json['seasons'] as List).map((i) => Season.fromJson(i)).toList(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'description': description,
    'posterUrl': posterUrl,
    'backdropUrl': backdropUrl,
    'rating': rating,
    'seasons': seasons.map((i) => i.toJson()).toList(),
  };
}

class Season {
  final int number;
  final String? title;
  final List<Episode> episodes;

  Season({
    required this.number,
    this.title,
    required this.episodes,
  });

  factory Season.fromJson(Map<String, dynamic> json) {
    return Season(
      number: json['number'],
      title: json['title'],
      episodes: (json['episodes'] as List).map((i) => Episode.fromJson(i)).toList(),
    );
  }

  Map<String, dynamic> toJson() => {
    'number': number,
    'title': title,
    'episodes': episodes.map((i) => i.toJson()).toList(),
  };
}

class Episode {
  final String id;
  final int episodeNumber;
  final String title;
  final String? description;
  final String? thumbnail;
  final List<StreamSource> sources;

  Episode({
    required this.id,
    required this.episodeNumber,
    required this.title,
    this.description,
    this.thumbnail,
    required this.sources,
  });

  factory Episode.fromJson(Map<String, dynamic> json) {
    return Episode(
      id: json['id'],
      episodeNumber: json['episodeNumber'],
      title: json['title'],
      description: json['description'],
      thumbnail: json['thumbnail'],
      sources: (json['sources'] as List).map((i) => StreamSource.fromJson(i)).toList(),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'episodeNumber': episodeNumber,
    'title': title,
    'description': description,
    'thumbnail': thumbnail,
    'sources': sources.map((i) => i.toJson()).toList(),
  };
}
