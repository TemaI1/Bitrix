document.addEventListener('DOMContentLoaded', function () {
	// Перемещаем модалки в body
	let modalCreate = document.getElementById('modalOverlay');
	let modalEdit = document.getElementById('modalOverlayEdit');
	let modalSolution = document.getElementById('modalOverlaySolution');
	let modalDecision = document.getElementById('modalOverlayDecision');

	if (modalCreate && modalCreate.parentNode !== document.body) {
		document.body.appendChild(modalCreate);
	}
	if (modalEdit && modalEdit.parentNode !== document.body) {
		document.body.appendChild(modalEdit);
	}
	if (modalSolution && modalSolution.parentNode !== document.body) {
		document.body.appendChild(modalSolution);
	}
	if (modalDecision && modalDecision.parentNode !== document.body) {
		document.body.appendChild(modalDecision);
	}

	// Перемещаем контейнер уведомлений и окно подтверждения в body
	const toastContainer = document.getElementById('toastContainer');
	const confirmOverlay = document.getElementById('confirmOverlay');

	if (toastContainer && toastContainer.parentNode !== document.body) {
		document.body.appendChild(toastContainer);
	}
	if (confirmOverlay && confirmOverlay.parentNode !== document.body) {
		document.body.appendChild(confirmOverlay);
	}

// =========================================================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ: уведомления и подтверждения
// =========================================================================

	/**
	 * Показывает всплывающее уведомление справа
	 */
	function notify(message, type = 'info', duration = 3500) {
		const container = document.getElementById('toastContainer');
		if (!container) return;

		const icons = {success: '✓', error: '✕', info: 'ℹ'};
		const toast = document.createElement('div');
		toast.className = 'toast toast-' + type;
		toast.innerHTML =
			'<span class="toast-icon">' + (icons[type] || icons.info) + '</span>' +
			'<span>' + message + '</span>';

		toast.addEventListener('click', () => hideToast(toast));
		container.appendChild(toast);

		if (duration > 0) {
			setTimeout(() => hideToast(toast), duration);
		}
	}

	function hideToast(toast) {
		if (!toast || toast.classList.contains('toast-hiding')) return;
		toast.classList.add('toast-hiding');
		setTimeout(() => toast.parentNode && toast.parentNode.removeChild(toast), 300);
	}

	/**
	 * Показывает кастомное окно подтверждения
	 */
	function askConfirm(message, title = 'Подтверждение') {
		return new Promise((resolve) => {
			const overlay = document.getElementById('confirmOverlay');
			const titleEl = document.getElementById('confirmTitle');
			const msgEl = document.getElementById('confirmMessage');
			const okBtn = document.getElementById('confirmOk');
			const cancelBtn = document.getElementById('confirmCancel');

			titleEl.textContent = title;
			msgEl.innerHTML = message;
			overlay.style.display = 'block';

			function close(result) {
				overlay.style.display = 'none';
				okBtn.removeEventListener('click', onOk);
				cancelBtn.removeEventListener('click', onCancel);
				overlay.removeEventListener('click', onBg);
				resolve(result);
			}

			function onOk() {
				close(true);
			}

			function onCancel() {
				close(false);
			}

			function onBg(e) {
				if (e.target === overlay) close(false);
			}

			okBtn.addEventListener('click', onOk);
			cancelBtn.addEventListener('click', onCancel);
			overlay.addEventListener('click', onBg);
		});
	}

// =========================================================================
// МОДАЛКА СОЗДАНИЯ
// =========================================================================
	const openBtn = document.getElementById('openModal');
	const closeBtn = document.getElementById('closeModal');
	const formCreate = document.getElementById('form-creation');

	function closeModalCreate() {
		modalCreate.style.display = 'none';
		document.body.style.overflow = '';
		formCreate.reset();
	}

	if (openBtn) {
		openBtn.addEventListener('click', () => {
			if (modalCreate) {
				modalCreate.style.display = 'block';
			}
			document.body.style.overflow = 'hidden';
		});
	}

	closeBtn.addEventListener('click', (e) => {
		e.stopPropagation();
		closeModalCreate();
	});

	modalCreate.addEventListener('click', (event) => {
		if (event.target === modalCreate) closeModalCreate();
	});

// =========================================================================
// МОДАЛКА РЕДАКТИРОВАНИЯ
// =========================================================================
	const closeBtnEdit = document.getElementById('closeModalEdit');
	const formEdit = document.getElementById('form-edit');
	const editModalTitle = document.getElementById('editModalTitle');
	const editRecordId = document.getElementById('edit-record-id');

	function closeModalEdit() {
		modalEdit.style.display = 'none';
		document.body.style.overflow = '';
		formEdit.reset();
	}

	closeBtnEdit.addEventListener('click', (e) => {
		e.stopPropagation();
		closeModalEdit();
	});

	modalEdit.addEventListener('click', (event) => {
		if (event.target === modalEdit) closeModalEdit();
	});

// =========================================================================
// МОДАЛКА РЕШЕНИЯ СОТРУДНИКА
// =========================================================================
	const closeBtnSolution = document.getElementById('closeModalSolution');
	const formSolution = document.getElementById('form-solution');
	const solutionModalTitle = document.getElementById('solutionModalTitle');
	const solutionRecordId = document.getElementById('solution-record-id');

	function closeModalSolution() {
		modalSolution.style.display = 'none';
		document.body.style.overflow = '';
		formSolution.reset();
	}

	closeBtnSolution.addEventListener('click', (e) => {
		e.stopPropagation();
		closeModalSolution();
	});

	modalSolution.addEventListener('click', (event) => {
		if (event.target === modalSolution) closeModalSolution();
	});

// =========================================================================
// МОДАЛКА РЕШЕНИЯ СОГЛАСУЮЩЕГО
// =========================================================================
	const closeBtnDecision = document.getElementById('closeModalDecision');
	const formDecision = document.getElementById('form-decision');
	const decisionModalTitle = document.getElementById('decisionModalTitle');
	const decisionRecordId = document.getElementById('decision-record-id');

	function closeModalDecision() {
		modalDecision.style.display = 'none';
		document.body.style.overflow = '';
		formDecision.reset();
	}

	closeBtnDecision.addEventListener('click', (e) => {
		e.stopPropagation();
		closeModalDecision();
	});

	modalDecision.addEventListener('click', (event) => {
		if (event.target === modalDecision) closeModalDecision();
	});

// =========================================================================
// ЗАКРЫТИЕ ПО ESCAPE
// =========================================================================
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			if (modalCreate.style.display === 'block') closeModalCreate();
			if (modalEdit.style.display === 'block') closeModalEdit();
			if (modalSolution.style.display === 'block') closeModalSolution();
			if (modalDecision.style.display === 'block') closeModalDecision();
		}
	});

// =========================================================================
// ОТКРЫТИЕ МОДАЛКИ РЕДАКТИРОВАНИЯ
// =========================================================================
	window.updateElem = function (recordId) {
		editModalTitle.textContent = 'Изменить несоответствие №' + recordId;
		editRecordId.value = recordId;
		modalEdit.style.display = 'block';
		document.body.style.overflow = 'hidden';
	};

// =========================================================================
// ОТКРЫТИЕ МОДАЛКИ РЕШЕНИЯ СОТРУДНИКА
// =========================================================================
	window.addSolution = function (recordId, currentRound) {
		// Приводим к числу, если не передано — считаем что это 0 круг
		const round = parseInt(currentRound) || 0;

		solutionModalTitle.textContent = 'Выполнить анализ несоответствия №' + recordId;
		solutionRecordId.value = recordId;

		// Находим блок согласующих
		const approversGroup = document.getElementById('approvers-form-group');
		if (approversGroup) {
			if (round === 0) {
				approversGroup.style.display = 'block'; // Показываем на 0 круге
			} else {
				approversGroup.style.display = 'none';  // Скрываем на 1, 2, 3 и т.д.
			}
		}

		modalSolution.style.display = 'block';
		document.body.style.overflow = 'hidden';
	};


// =========================================================================
// ОТКРЫТИЕ МОДАЛКИ РЕШЕНИЯ СОГЛАСУЮЩЕГО
// =========================================================================
	window.decisionApproval = function (recordId) {
		decisionModalTitle.textContent = 'Принять решение о согласовании несоответствия №' + recordId;
		decisionRecordId.value = recordId;
		modalDecision.style.display = 'block';
		document.body.style.overflow = 'hidden';
	};

// =========================================================================
// ОТПРАВКА ФОРМЫ СОЗДАНИЯ
// =========================================================================
	formCreate.addEventListener('submit', (event) => {
		event.preventDefault();
		if (!formCreate.checkValidity()) return;

		const submitBtn = formCreate.querySelector('.submit-btn');
		const originalBtnText = submitBtn.textContent;
		submitBtn.disabled = true;
		submitBtn.textContent = 'Отправка...';

		BX.ajax({
			method: 'POST',
			url: '/nonconformity-analysis/ajax/creating-record.php',
			data: new FormData(formCreate),
			dataType: 'json',
			preparePost: false,
			onsuccess: function (response) {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;

				try {
					if (typeof response === 'string') response = JSON.parse(response);

					if (response.status === 'success') {
						notify('Несоответствие успешно создано', 'success');
						formCreate.reset();
						closeModalCreate();
						setTimeout(() => location.reload(), 500);
					} else {
						notify(response.message || 'Ошибка при создании', 'error', 5000);
					}
				} catch (e) {
					notify('Некорректный ответ от сервера', 'error', 5000);
				}
			},
			onfailure: function () {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;
				notify('Не удалось связаться с сервером', 'error', 5000);
			}
		});
	});

// =========================================================================
// ОТПРАВКА ФОРМЫ РЕДАКТИРОВАНИЯ
// =========================================================================
	formEdit.addEventListener('submit', (event) => {
		event.preventDefault();
		if (!formEdit.checkValidity()) return;

		const submitBtn = formEdit.querySelector('.submit-btn');
		const originalBtnText = submitBtn.textContent;
		submitBtn.disabled = true;
		submitBtn.textContent = 'Сохранение...';

		BX.ajax({
			method: 'POST',
			url: '/nonconformity-analysis/ajax/update-record.php',
			data: new FormData(formEdit),
			dataType: 'json',
			preparePost: false,
			onsuccess: function (response) {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;

				try {
					if (typeof response === 'string') response = JSON.parse(response);

					if (response.status === 'success') {
						notify(response.message || 'Запись обновлена', 'success');
						formEdit.reset();
						closeModalEdit();
						setTimeout(() => location.reload(), 500);
					} else {
						notify(response.message || 'Ошибка при обновлении', 'error', 5000);
					}
				} catch (e) {
					notify('Некорректный ответ от сервера', 'error', 5000);
				}
			},
			onfailure: function () {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;
				notify('Не удалось связаться с сервером', 'error', 5000);
			}
		});
	});

// =========================================================================
// ОТПРАВКА ФОРМЫ РЕШЕНИЯ СОТРУДНИКА
// =========================================================================
	formSolution.addEventListener('submit', (event) => {
		event.preventDefault();
		if (!formSolution.checkValidity()) return;

		const submitBtn = formSolution.querySelector('.submit-btn');
		const originalBtnText = submitBtn.textContent;
		submitBtn.disabled = true;
		submitBtn.textContent = 'Отправка...';

		BX.ajax({
			method: 'POST',
			url: '/nonconformity-analysis/ajax/add-solution-record.php',
			data: new FormData(formSolution),
			dataType: 'json',
			preparePost: false,
			onsuccess: function (response) {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;

				try {
					if (typeof response === 'string') response = JSON.parse(response);

					if (response.status === 'success') {
						notify(response.message || 'Запись обновлена', 'success');
						formSolution.reset();
						closeModalSolution();
						setTimeout(() => location.reload(), 500);
					} else {
						notify(response.message || 'Ошибка при обновлении', 'error', 5000);
					}
				} catch (e) {
					notify('Некорректный ответ от сервера', 'error', 5000);
				}
			},
			onfailure: function () {
				submitBtn.disabled = false;
				submitBtn.textContent = originalBtnText;
				notify('Не удалось связаться с сервером', 'error', 5000);
			}
		});
	});

// =========================================================================
// ОТПРАВКА ФОРМЫ РЕШЕНИЯ СОГЛАСУЮЩЕГО
// =========================================================================
	formDecision.addEventListener('submit', (event) => {
		event.preventDefault();
		if (!formDecision.checkValidity()) return;

		// Находим кнопку, на которую нажал пользователь (submitter)
		const clickedBtn = event.submitter;
		if (!clickedBtn) return;
		// Достаем экшен ('approve' или 'reject')
		const actionType = clickedBtn.getAttribute('data-action');
		// Блокируем и меняем текст именно на нажатой кнопке
		const originalBtnText = clickedBtn.textContent;
		clickedBtn.disabled = true;
		clickedBtn.textContent = 'Отправка...';

		// Формируем FormData и подмешиваем экшен
		const formData = new FormData(formDecision);
		formData.append('action', actionType);

		BX.ajax({
			method: 'POST',
			url: '/nonconformity-analysis/ajax/decision-record.php',
			data: formData, // Передаем модифицированный объект
			dataType: 'json',
			preparePost: false,
			onsuccess: function (response) {
				// Разблокируем именно ту кнопку, на которую нажимали
				clickedBtn.disabled = false;
				clickedBtn.textContent = originalBtnText;

				try {
					if (typeof response === 'string') response = JSON.parse(response);

					if (response.status === 'success') {
						notify(response.message || 'Запись обновлена', 'success');
						formDecision.reset();
						closeModalDecision();
						setTimeout(() => location.reload(), 500);
					} else {
						notify(response.message || 'Ошибка при обновлении', 'error', 5000);
					}
				} catch (e) {
					notify('Некорректный ответ от сервера', 'error', 5000);
				}
			},
			onfailure: function () {
				// Разблокируем именно ту кнопку, на которую нажимали
				clickedBtn.disabled = false;
				clickedBtn.textContent = originalBtnText;
				notify('Не удалось связаться с сервером', 'error', 5000);
			}
		});
	});

// =========================================================================
// УДАЛЕНИЕ ЗАПИСИ
// =========================================================================
	const deleteBtn = document.getElementById('deleteRecordBtn');

	if (deleteBtn) {
		deleteBtn.addEventListener('click', async () => {
			const recordId = editRecordId.value;
			if (!recordId) {
				notify('Не выбрана запись для удаления', 'error');
				return;
			}

			const confirmed = await askConfirm(
				'Вы уверены, что хотите удалить несоответствие №' + recordId + '?<br><br><strong>Это действие нельзя отменить!</strong>',
				'Удаление записи'
			);

			if (!confirmed) return;

			const originalBtnText = deleteBtn.textContent;
			deleteBtn.disabled = true;
			deleteBtn.textContent = 'Удаление...';

			BX.ajax({
				method: 'POST',
				url: '/nonconformity-analysis/ajax/delete-record.php',
				data: {record_id: recordId},
				dataType: 'json',
				onsuccess: function (response) {
					deleteBtn.disabled = false;
					deleteBtn.textContent = originalBtnText;

					try {
						if (typeof response === 'string') response = JSON.parse(response);

						if (response.status === 'success') {
							notify('Запись №' + recordId + ' удалена', 'success');
							closeModalEdit();
							setTimeout(() => location.reload(), 500);
						} else {
							notify(response.message || 'Ошибка при удалении', 'error', 5000);
						}
					} catch (e) {
						notify('Некорректный ответ от сервера', 'error', 5000);
					}
				},
				onfailure: function () {
					deleteBtn.disabled = false;
					deleteBtn.textContent = originalBtnText;
					notify('Не удалось связаться с сервером', 'error', 5000);
				}
			});
		});
	}

// =========================================================================
// Логика накопления файлов
// =========================================================================
	const dropZone = document.getElementById("drop-zone");
	const fileInput = document.getElementById("solution-files");
	const fileList = document.getElementById("file-list");

	if (dropZone && fileInput && fileList) { // чтобы код не выдавал ошибку, если формы нет на какой-то из страниц
		let allFiles = [];

		dropZone.addEventListener("click", () => fileInput.click());

		['dragenter', 'dragover'].forEach(eventName => {
			dropZone.addEventListener(eventName, (e) => { e.preventDefault(); dropZone.classList.add('dragover'); }, false);
		});
		['dragleave', 'drop'].forEach(eventName => {
			dropZone.addEventListener(eventName, (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); }, false);
		});

		dropZone.addEventListener("drop", (e) => {
			const dt = e.dataTransfer;
			handleFiles(dt.files);
		});

		fileInput.addEventListener("change", (e) => {
			handleFiles(e.target.files);
		});

		function handleFiles(files) {
			for (let i = 0; i < files.length; i++) {
				allFiles.push(files[i]);
			}
			updateInterface();
		}

		function updateInterface() {
			fileList.innerHTML = "";
			const dataTransfer = new DataTransfer();

			allFiles.forEach((file, index) => {
				dataTransfer.items.add(file);

				const fileItem = document.createElement("div");
				fileItem.className = "file-item";
				fileItem.innerHTML = `
                    <span>${file.name} (${(file.size / 1024).toFixed(1)} КБ)</span>
                    <button type="button" class="remove-file-btn" data-index="${index}">&times;</button>
                `;
				fileList.appendChild(fileItem);
			});

			fileInput.files = dataTransfer.files;
		}

		fileList.addEventListener("click", function(e) {
			if (e.target.classList.contains("remove-file-btn")) {
				const indexToRemove = parseInt(e.target.getAttribute("data-index"));
				allFiles.splice(indexToRemove, 1);
				updateInterface();
			}
		});
	}

});
