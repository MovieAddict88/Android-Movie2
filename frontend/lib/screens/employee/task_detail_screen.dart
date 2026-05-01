import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

class TaskDetailScreen extends StatefulWidget {
  final Map<String, dynamic> task;

  const TaskDetailScreen({super.key, required this.task});

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

class _TaskDetailScreenState extends State<TaskDetailScreen> {
  late VideoPlayerController _controller;
  bool _isCompleted = false;
  bool _isOffline = false;

  @override
  void initState() {
    super.initState();
    _isCompleted = widget.task['video_completed'] ?? false;

    if (widget.task['video_url'] != null) {
      _controller = VideoPlayerController.networkUrl(
        Uri.parse(widget.task['video_url']),
      )..initialize().then((_) {
          if (mounted) setState(() {});
          _controller.addListener(_checkVideoCompletion);
        }).catchError((e) {
          setState(() { _isOffline = true; });
        });
    }
  }

  void _checkVideoCompletion() {
    if (!_controller.value.isInitialized) return;

    final position = _controller.value.position;
    final duration = _controller.value.duration;

    if (position >= duration * 0.9 && !_isCompleted) {
      setState(() {
        _isCompleted = true;
      });
      _onTaskCompleted();
    }
  }

  void _onTaskCompleted() {
    // Logic for local storage if offline
    if (_isOffline) {
      // TODO: Save to SQFlite for later sync
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Completed offline. Will sync when online.')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Video Training Completed!')),
      );
    }
  }

  @override
  void dispose() {
    if (widget.task['video_url'] != null) {
      _controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Use Theme.of(context).primaryColor for white-labeling instead of hardcoded colors
    final primaryColor = Theme.of(context).primaryColor;

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.task['title'] ?? 'Task Details'),
        backgroundColor: primaryColor,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.task['title'] ?? '',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 8),
            Chip(
              label: Text(widget.task['category'] ?? 'General'),
              backgroundColor: primaryColor.withOpacity(0.1),
            ),
            const SizedBox(height: 16),
            Text(
              widget.task['description'] ?? 'No description provided.',
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 24),
            if (widget.task['video_url'] != null) ...[
              Text(
                'Training Video',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 8),
              _isOffline
                ? const Card(child: Padding(padding: EdgeInsets.all(16), child: Text("Video unavailable offline.")))
                : _controller.value.isInitialized
                  ? AspectRatio(
                      aspectRatio: _controller.value.aspectRatio,
                      child: Stack(
                        alignment: Alignment.bottomCenter,
                        children: [
                          VideoPlayer(_controller),
                          VideoProgressIndicator(_controller, allowScrubbing: true, colors: VideoProgressColors(playedColor: primaryColor)),
                          Center(
                            child: IconButton(
                              icon: Icon(
                                _controller.value.isPlaying ? Icons.pause : Icons.play_arrow,
                                color: Colors.white,
                                size: 50,
                              ),
                              onPressed: () {
                                setState(() {
                                  _controller.value.isPlaying ? _controller.pause() : _controller.play();
                                });
                              },
                            ),
                          ),
                        ],
                      ),
                    )
                  : const Center(child: CircularProgressIndicator()),
              if (_isCompleted)
                Padding(
                  padding: const EdgeInsets.only(top: 8.0),
                  child: Row(
                    children: [
                      const Icon(Icons.check_circle, color: Colors.green),
                      const SizedBox(width: 8),
                      Text('Training Requirement Met', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
            ],
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () {
                // TODO: Implement camera scan / Digital Signature
              },
              icon: const Icon(Icons.camera_alt),
              label: const Text('Capture & Sign Document'),
              style: ElevatedButton.styleFrom(
                backgroundColor: primaryColor,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50)
              ),
            ),
          ],
        ),
      ),
    );
  }
}
