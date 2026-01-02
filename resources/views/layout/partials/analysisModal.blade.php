<div class="modal fade" id="analysisModal" tabindex="-1" aria-labelledby="analysisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h4 class="modal-title fw-bold text-danger" id="analysisModalLabel">
                    <i class="fas fa-robot mr-2"></i>AI Phân Tích
                </h4>
                </div>

            <div class="modal-body px-4 py-3">
                <div id="analysis-loading" class="text-center py-5">
                    <p class="mt-3 fs-5 text-muted">Đang phân tích thế cờ...</p>
                </div>

                <div id="analysis-result" style="display: none;">

                    <div class="mb-4 text-center">
                        <h5 class="text-uppercase text-secondary small fw-bold ls-1">Đánh giá cục diện</h5>
                        <h2 id="ai-evaluation" class="fw-bold text-dark mb-0"></h2>
                    </div>

                    <hr class="opacity-25 my-4">

                    <div class="mb-4">
                        <h5 class="fw-bold text-dark mb-3">
                            <i class="fas fa-chess-knight mr-2"></i>Nước đi tối ưu:
                        </h5>
                        <div id="ai-best-moves" class="d-flex flex-wrap gap-2 text-light"></div>
                    </div>

                    <div class="card bg-light border-0 rounded-3">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">
                                <i class="fas fa-comment-alt mr-2"></i>Bình luận chi tiết:
                            </h5>
                            <div id="ai-analysis-text" class="text-dark" style="font-size: 1.1rem; line-height: 1.8; text-align: justify;">
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function openAnalysisModal(fenData) {
        const fenToSend = (typeof fenData === 'object' && fenData.fen) ? fenData.fen : fenData;
        const myAnalysisModal = new bootstrap.Modal(document.getElementById('analysisModal'));

        myAnalysisModal.show();

        // Reset UI
        document.getElementById('analysis-loading').style.display = 'block';
        document.getElementById('analysis-result').style.display = 'none';

        try {
            const response = await fetch('/api/chess/analyze', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fen: fenToSend })
            });

            const result = await response.json();

            if (result.success && result.data) {
                renderAnalysisData(result.data);
            } else {
                throw new Error('API returned error');
            }

        } catch (error) {
            console.error(error);
            document.getElementById('analysis-loading').style.display = 'none';
        }
    }

    function renderAnalysisData(data) {
        document.getElementById('analysis-loading').style.display = 'none';
        document.getElementById('analysis-result').style.display = 'block';

        // 1. Render Evaluation Headline
        // Check if data.evaluation exists, otherwise fallback
        document.getElementById('ai-evaluation').innerText = data.evaluation || "Đã có kết quả";

        // 2. Render Analysis Text
        document.getElementById('ai-analysis-text').innerHTML = data.analysis;

        // 3. Render Best Moves as Chips/Badges
        const movesContainer = document.getElementById('ai-best-moves');
        movesContainer.innerHTML = ''; // Clear previous

        if (data.best_moves && data.best_moves.length > 0) {
            data.best_moves.forEach(move => {
                const badge = document.createElement('span');
                // Styling for the chips
                badge.className = 'badge bg-danger rounded-pill px-3 py-2 fs-6 shadow-sm mr-2';
                badge.innerText = move;
                movesContainer.appendChild(badge);
            });
        } else {
            movesContainer.innerHTML = '<span class="text-muted fst-italic">Không có gợi ý cụ thể.</span>';
        }
    }
</script>
