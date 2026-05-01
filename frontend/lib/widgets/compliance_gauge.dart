import 'package:flutter/material.dart';
import 'dart:math' as math;

class ComplianceGauge extends StatelessWidget {
  final double score; // 0 to 100
  final double size;

  const ComplianceGauge({
    super.key,
    required this.score,
    this.size = 200,
  });

  Color _getScoreColor(BuildContext context) {
    if (score >= 90) return Colors.green;
    if (score >= 70) return Colors.orange;
    // Use theme error color or red
    return Theme.of(context).colorScheme.error;
  }

  @override
  Widget build(BuildContext context) {
    final color = _getScoreColor(context);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          width: size,
          height: size / 1.5,
          child: CustomPaint(
            painter: _GaugePainter(score: score, color: color),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          '${score.toInt()}',
          style: TextStyle(
            fontSize: size / 4,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        const Text(
          'Compliance Score',
          style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w500),
        ),
      ],
    );
  }
}

class _GaugePainter extends CustomPainter {
  final double score;
  final Color color;

  _GaugePainter({required this.score, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height);
    final radius = size.width / 2;

    // Background track
    final trackPaint = Paint()
      ..color = Colors.grey.withOpacity(0.2)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 20
      ..strokeCap = StrokeCap.round;

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      math.pi,
      math.pi,
      false,
      trackPaint,
    );

    // Progress arc
    final progressPaint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 20
      ..strokeCap = StrokeCap.round;

    final sweepAngle = (score / 100) * math.pi;
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      math.pi,
      sweepAngle,
      false,
      progressPaint,
    );

    // Needle
    final needlePaint = Paint()
      ..color = Colors.black87
      ..style = PaintingStyle.fill;

    final needleAngle = math.pi + sweepAngle;
    final needlePath = Path()
      ..moveTo(center.dx, center.dy)
      ..lineTo(
        center.dx + (radius - 30) * math.cos(needleAngle),
        center.dy + (radius - 30) * math.sin(needleAngle),
      );

    canvas.drawPath(needlePath, needlePaint..style = PaintingStyle.stroke..strokeWidth = 3);
    canvas.drawCircle(center, 5, needlePaint..style = PaintingStyle.fill);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}
