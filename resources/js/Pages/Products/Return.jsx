// resources/js/Pages/ReturnForm.jsx
import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function ReturnForm() {
  const [orderId, setOrderId] = useState('');
  const [reason, setReason] = useState('');
  const [message, setMessage] = useState('');
  const [guideUrl, setGuideUrl] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const response = await axios.post('/api/returns', {
        order_id: orderId,
        reason: reason,
      });

      const data = response.data;
      setMessage(`Estado: ${data.status}`);
      if (data.guide_url) setGuideUrl(data.guide_url);
    } catch (error) {
      setMessage('Error al enviar la solicitud');
    }
  };

  return (
    <>
      <Head title="Solicitar Devolución" />
      <div className="container mt-4">
        <h2>Solicitar Devolución</h2>
        <form onSubmit={handleSubmit} className="mt-3">
          <div className="mb-3">
            <label className="form-label">ID del Pedido</label>
            <input
              type="text"
              className="form-control"
              value={orderId}
              onChange={(e) => setOrderId(e.target.value)}
              required
            />
          </div>

          <div className="mb-3">
            <label className="form-label">Motivo</label>
            <textarea
              className="form-control"
              rows="3"
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              required
            ></textarea>
          </div>

          <button className="btn btn-primary" type="submit">
            Enviar Solicitud
          </button>
        </form>

        {message && <div className="alert alert-info mt-3">{message}</div>}

        {guideUrl && (
          <a
            href={guideUrl}
            download
            className="btn btn-success mt-2"
            target="_blank"
            rel="noopener noreferrer"
          >
            Descargar Guía
          </a>
        )}
      </div>
    </>
  );
}
