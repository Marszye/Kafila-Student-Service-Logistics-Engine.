<!-- Payment Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Payment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h4>Total Payment: <span id="modalFee"></span></h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-pay-cash">Cash</button>
                    <button type="button" class="btn btn-primary btn-pay-debt">Debt</button>
                </div>
            </div>
            <form id="completeForm" method="POST" action="complete.php">
                <input type="hidden" name="id" id="modalId">
                <input type="hidden" name="method" id="modalMethod">
            </form>
        </div>
    </div>
</div>